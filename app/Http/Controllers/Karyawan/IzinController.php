<?php

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use App\Models\{Izin, Notifikasi, User};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class IzinController extends Controller
{
    public function index(Request $request)
    {
        $karyawan = auth()->user()->karyawan;

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

        return view('karyawan.izin', compact(
            'karyawan',
            'riwayatIzin',
            'stats',
            'rekapBulan',
            'sisaCuti',
            'totalCuti'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'jenis_izin'      => 'required|in:izin,sakit,cuti,tugas_luar,alpa',
            'tanggal_mulai'   => 'required|date|after_or_equal:today',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'keterangan'      => 'required|string|max:1000',
            'lampiran'        => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $karyawan = auth()->user()->karyawan;
        $lampiranPath = null;

        if ($request->hasFile('lampiran')) {
            $lampiranPath = $request->file('lampiran')->store("izin/{$karyawan->id}", 'public');
        }

        $jenisLabel = ucfirst(str_replace('_', ' ', $validated['jenis_izin']));

        $izin = Izin::create([
            'karyawan_id'     => $karyawan->id,
            'jenis_izin'      => $validated['jenis_izin'],
            'tanggal_mulai'   => $validated['tanggal_mulai'],
            'tanggal_selesai' => $validated['tanggal_selesai'],
            'keterangan'      => $validated['keterangan'],
            'lampiran'        => $lampiranPath,
            'status'          => 'pending',
        ]);

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

        return back()->with('success', 'Pengajuan izin berhasil dikirim.');
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
}
