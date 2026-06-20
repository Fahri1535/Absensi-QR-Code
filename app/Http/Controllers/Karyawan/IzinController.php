<?php

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use App\Models\{Izin, Notifikasi, Presensi, User};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class IzinController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $karyawan = $user->karyawan;

        if (!$karyawan) {
            // Jika admin/hrd belum punya record karyawan, buatkan default agar tidak error
            $karyawan = \App\Models\Karyawan::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'nama_lengkap' => ucfirst($user->role) . ' Admin',
                    'status' => 'aktif',
                    'kode_karyawan' => 'ADM-' . \Illuminate\Support\Str::upper(\Illuminate\Support\Str::random(12)),
                ]
            );
        }

        $riwayatIzin = Izin::where('karyawan_id', $karyawan->id)
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        $stats = [
            'pending'   => Izin::where('karyawan_id', $karyawan->id)->where('status', 'pending')->count(),
            'disetujui' => Izin::where('karyawan_id', $karyawan->id)->where('status', 'disetujui')->count(),
            'ditolak'   => Izin::where('karyawan_id', $karyawan->id)->where('status', 'ditolak')->count(),
        ];

        [$rekapBulan, $sisaCuti, $totalCuti] = $this->rekapUntukSidebar($karyawan->id);

        // Cek apakah sedang dalam periode izin yang disetujui
        $sedangIzin = Izin::where('karyawan_id', $karyawan->id)
            ->where('status', 'disetujui')
            ->where('tanggal_mulai', '<=', today())
            ->where('tanggal_selesai', '>=', today())
            ->first();
        
        // Cek apakah memiliki izin yang masih pending
        $izinPending = Izin::where('karyawan_id', $karyawan->id)
            ->where('status', 'pending')
            ->first();

        // Cek apakah karyawan alpa hari ini (tidak hadir tanpa izin disetujui
        // di hari kerja). Konsisten dengan cara sistem menghitung "alpa".
        $sedangAlpaHariIni = $this->cekAlpaHariIni($karyawan->id);

        return view('karyawan.izin', compact(
            'karyawan',
            'riwayatIzin',
            'stats',
            'rekapBulan',
            'sisaCuti',
            'totalCuti',
            'sedangIzin',
            'izinPending',
            'sedangAlpaHariIni'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'jenis_izin'      => 'required|in:izin,sakit,cuti,pulang_cepat,lembur,tugas_luar',
            'tanggal_mulai'   => 'required|date|after_or_equal:today',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'keterangan'      => 'required|string|max:1000',
            'lampiran'        => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $user = auth()->user();
        $karyawan = $user->karyawan;

        $sudahMengajuHariIni = Izin::where('karyawan_id', $karyawan->id)
            ->whereDate('created_at', today())
            ->exists();

        if ($sudahMengajuHariIni) {
            return back()
                ->withErrors([
                    'izin_per_hari' => 'Anda sudah mengajukan izin hari ini. Maksimal satu pengajuan per hari kalender.',
                ])
                ->withInput();
        }

        // Cek apakah masih memiliki izin pending
        $izinPending = Izin::where('karyawan_id', $karyawan->id)
            ->where('status', 'pending')
            ->first();
        if ($izinPending) {
            return back()
                ->withErrors([
                    'izin_pending' => "Anda masih memiliki izin yang menunggu persetujuan dari {$izinPending->tanggal_mulai->format('d M Y')} sampai {$izinPending->tanggal_selesai->format('d M Y')}. Silakan tunggu hingga izin tersebut diproses sebelum mengajukan izin baru.",
                ])
                ->withInput();
        }

        // Validasi: karyawan yang sudah alpa hari ini tidak bisa mengajukan izin
        // untuk hari ini (tidak bisa "menutupi" ketidakhadiran dengan izin
        // setelah faktanya). Pengajuan untuk hari berikutnya tetap diperbolehkan.
        if ($this->cekAlpaHariIni($karyawan->id)) {
            $tanggalMulaiInput = \Carbon\Carbon::parse($validated['tanggal_mulai']);
            if ($tanggalMulaiInput->isToday()) {
                return back()
                    ->withErrors([
                        'alpa' => 'Anda tercatat alpa hari ini (tidak hadir tanpa keterangan). Pengajuan izin untuk hari ini tidak dapat diproses. Silakan hubungi HRD.',
                    ])
                    ->withInput();
            }
        }

        // Validasi tanggal izin tidak boleh bertumpang tindih dengan izin yang sudah disetujui
        $tanggalMulai = \Carbon\Carbon::parse($validated['tanggal_mulai']);
        $tanggalSelesai = \Carbon\Carbon::parse($validated['tanggal_selesai']);
        
        $izinBertumpangTindih = Izin::where('karyawan_id', $karyawan->id)
            ->where('status', 'disetujui')
            ->where('tanggal_mulai', '<=', $tanggalSelesai)
            ->where('tanggal_selesai', '>=', $tanggalMulai)
            ->first();

        if ($izinBertumpangTindih) {
            return back()
                ->withErrors([
                    'izin_bertumpang' => "Anda sudah memiliki izin yang berlaku dari {$izinBertumpangTindih->tanggal_mulai->format('d M Y')} sampai {$izinBertumpangTindih->tanggal_selesai->format('d M Y')}. Silakan pilih tanggal yang berbeda.",
                ])
                ->withInput();
        }

        // Validasi izin sakit lebih dari 2 hari harus menyertakan surat dokter
        if ($validated['jenis_izin'] === 'sakit') {
            $durasi = $tanggalMulai->diffInDays($tanggalSelesai) + 1;
            if ($durasi > 2 && !$request->hasFile('lampiran')) {
                return back()
                    ->withErrors([
                        'lampiran' => 'Izin sakit lebih dari 2 hari wajib menyertakan surat dokter (pdf/jpg/jpeg/png).',
                    ])
                    ->withInput();
            }
        }

        // Validasi cuti tahunan maksimal 12 hari
        if ($validated['jenis_izin'] === 'cuti') {
            $totalCuti = 12;
            $pakaiCutiTahun = Izin::where('karyawan_id', $karyawan->id)
                ->where('jenis_izin', 'cuti')
                ->where('status', 'disetujui')
                ->whereYear('tanggal_mulai', now()->year)
                ->get()
                ->sum(fn ($i) => $i->tanggal_mulai->diffInDays($i->tanggal_selesai) + 1);

            $tanggalMulai = \Carbon\Carbon::parse($validated['tanggal_mulai']);
            $tanggalSelesai = \Carbon\Carbon::parse($validated['tanggal_selesai']);
            $durasiBaru = $tanggalMulai->diffInDays($tanggalSelesai) + 1;

            if (($pakaiCutiTahun + $durasiBaru) > $totalCuti) {
                $sisaCuti = $totalCuti - $pakaiCutiTahun;
                return back()
                    ->withErrors([
                        'cuti_limit' => "Anda hanya memiliki sisa {$sisaCuti} hari cuti tahun ini. Pengajuan cuti baru tidak boleh melebihi sisa cuti.",
                    ])
                    ->withInput();
            }
        }
        $lampiranPath = null;

        if ($request->hasFile('lampiran')) {
            $lampiranPath = $request->file('lampiran')->store("izin/{$karyawan->id}", 'public');
        }

        $jenisLabel = ucfirst(str_replace('_', ' ', $validated['jenis_izin']));
        
        // Auto-approve logic
        $isHrd = $user->role === 'hrd';
        $autoApprovedTypes = ['lembur', 'tugas_luar'];
        $isAutoApproved = $isHrd || in_array($validated['jenis_izin'], $autoApprovedTypes);

        $izin = Izin::create([
            'karyawan_id'     => $karyawan->id,
            'jenis_izin'      => $validated['jenis_izin'],
            'tanggal_mulai'   => $validated['tanggal_mulai'],
            'tanggal_selesai' => $validated['tanggal_selesai'],
            'keterangan'      => $validated['keterangan'],
            'lampiran'        => $lampiranPath,
            'status'          => $isAutoApproved ? 'disetujui' : 'pending',
        ]);

        // Only send notification if it's not auto-approved and user is not HRD
        if (!$isAutoApproved) {
            foreach (User::whereIn('role', ['hrd', 'operator'])->get()->unique('id') as $admin) {
                $link = match ($admin->role) {
                    'hrd'      => route('hrd.izin'),
                    'operator' => route('operator.dashboard'),
                    default    => route('login'),
                };

                Notifikasi::create([
                    'user_id' => $admin->id,
                    'judul'   => 'Pengajuan Izin Baru',
                    'pesan'   => "{$karyawan->nama_lengkap} mengajukan {$jenisLabel} · {$izin->tanggal_mulai->format('d M Y')} → {$izin->tanggal_selesai->format('d M Y')}",
                    'ikon'    => 'fa-file-circle-plus',
                    'warna'   => 'amber',
                    'link'    => $link,
                ]);
            }
            $successMessage = 'Pengajuan izin berhasil dikirim, menunggu persetujuan HRD.';
        } else {
            $successMessage = 'Pengajuan izin berhasil diajukan dan otomatis disetujui!';
        }

        return back()->with('success', $successMessage);
    }

    public function cancel(int $id)
    {
        $karyawan = auth()->user()->karyawan;
        $izin = Izin::where('karyawan_id', $karyawan->id)
            ->where('status', 'pending')
            ->findOrFail($id);

        if ($izin->lampiran) {
            Storage::disk('public')->delete($izin->lampiran);
        }

        $izin->delete();

        return back()->with('success', 'Pengajuan izin dibatalkan.');
    }

    /** @return array{0: array<string,int>, 1: int, 2: int} */
    protected function rekapUntukSidebar(int $karyawanId): array
    {
        $rekapKeys = ['izin' => 0, 'sakit' => 0, 'cuti' => 0, 'tugas_luar' => 0, 'alpa' => 0];
        $mStart = now()->copy()->startOfMonth();
        $mEnd = now()->copy()->endOfMonth();

        $list = Izin::where('karyawan_id', $karyawanId)
            ->where('status', 'disetujui')
            ->get(['jenis_izin', 'tanggal_mulai', 'tanggal_selesai']);

        foreach ($list as $row) {
            $overlap = self::overlapDaysInclusive($row->tanggal_mulai, $row->tanggal_selesai, $mStart, $mEnd);
            if ($overlap <= 0) {
                continue;
            }
            $j = $row->jenis_izin;
            if (isset($rekapKeys[$j])) {
                $rekapKeys[$j] += $overlap;
            }
        }

        $totalCuti = 12;
        $pakaiCutiTahun = Izin::where('karyawan_id', $karyawanId)
            ->where('jenis_izin', 'cuti')
            ->where('status', 'disetujui')
            ->whereYear('tanggal_mulai', now()->year)
            ->get()
            ->sum(fn ($i) => $i->tanggal_mulai->diffInDays($i->tanggal_selesai) + 1);

        $sisaCuti = max(0, $totalCuti - min($pakaiCutiTahun, $totalCuti));

        return [$rekapKeys, $sisaCuti, $totalCuti];
    }

    protected static function overlapDaysInclusive(\Carbon\CarbonInterface $aStart, \Carbon\CarbonInterface $aEnd, \Carbon\CarbonInterface $bStart, \Carbon\CarbonInterface $bEnd): int
    {
        $start = $aStart->greaterThan($bStart) ? $aStart->copy() : $bStart->copy();
        $end = $aEnd->lessThan($bEnd) ? $aEnd->copy() : $bEnd->copy();

        if ($start->greaterThan($end)) {
            return 0;
        }

        return (int) $start->diffInDays($end) + 1;
    }

    /**
     * Cek apakah karyawan alpa pada hari ini.
     *
     * Alpa = hari kerja (bukan Sabtu/Minggu) DAN tidak ada baris presensi
     * dengan jam_datang DAN tidak ada izin "disetujui" yang mencakup hari ini.
     * Konsisten dengan cara sistem menghitung "alpa" di controller lain.
     */
    protected function cekAlpaHariIni(int $karyawanId): bool
    {
        $hariIni = today();

        // Weekend tidak dihitung sebagai alpa
        if ($hariIni->isWeekend()) {
            return false;
        }

        // Sudah presensi (absen masuk) hari ini -> tidak alpa
        $sudahPresensi = Presensi::where('karyawan_id', $karyawanId)
            ->whereDate('tanggal', $hariIni)
            ->whereNotNull('jam_datang')
            ->exists();
        if ($sudahPresensi) {
            return false;
        }

        // Punya izin yang disetujui mencakup hari ini -> tidak alpa
        $adaIzinDisetujui = Izin::where('karyawan_id', $karyawanId)
            ->where('status', 'disetujui')
            ->where('tanggal_mulai', '<=', $hariIni)
            ->where('tanggal_selesai', '>=', $hariIni)
            ->exists();
        if ($adaIzinDisetujui) {
            return false;
        }

        return true;
    }
}
