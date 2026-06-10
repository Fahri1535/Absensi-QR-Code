<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\{Karyawan, Presensi, Bantuan};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Hash, Storage, DB};
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $karyawan = $user->karyawan;

        // Ambil data dari Pusat Bantuan untuk inisialisasi awal jika record karyawan belum lengkap
        $bantuan = null;
        if ($user->role === 'operator' || $user->role === 'hrd') {
            $bantuan = Bantuan::where('slug', $user->role)->first();
        }

        // Auto-create record karyawan untuk Operator/HRD jika belum ada
        if (!$karyawan && ($user->role === 'operator' || $user->role === 'hrd')) {
            $karyawan = Karyawan::create([
                'user_id' => $user->id,
                'nama_lengkap' => $bantuan->nama ?? (ucfirst($user->role) . ' Admin'),
                'nomor_telepon' => $bantuan->telepon ?? null,
                'status' => 'aktif',
                'kode_karyawan' => 'ADM-' . Str::upper(Str::random(12)),
            ]);
        } 
        // Jika sudah ada record tapi nomor telepon/nama masih default, sinkronkan dari bantuan
        elseif ($karyawan && ($user->role === 'operator' || $user->role === 'hrd') && $bantuan) {
            if (empty($karyawan->nomor_telepon) && !empty($bantuan->telepon)) {
                $karyawan->update(['nomor_telepon' => $bantuan->telepon]);
            }
            if ($karyawan->nama_lengkap === (ucfirst($user->role) . ' Admin') && !empty($bantuan->nama)) {
                $karyawan->update(['nama_lengkap' => $bantuan->nama]);
            }
        }

        $qrCode = null;
        $qrImage = null;

        if ($karyawan && ($user->role === 'karyawan' || $user->role === 'hrd')) {
            $qrCode = $karyawan->kode_karyawan ?? $karyawan->id;
            $qrUrl  = route('karyawan.profile-public', ['kode_karyawan' => $qrCode]);
            $qrImage = QrCode::format('svg')->size(150)->generate($qrUrl);
        }

        // Statistik singkat untuk dashboard profil
        $stats = [];
        if ($karyawan && ($user->role === 'karyawan' || $user->role === 'hrd')) {
            $month = now()->month;
            $year  = now()->year;

            $dataPresensi = Presensi::where('karyawan_id', $karyawan->id)
                ->whereYear('tanggal', $year)
                ->whereMonth('tanggal', $month)
                ->get();

            $dataIzin = $karyawan->izin()
                ->where('status', 'disetujui')
                ->where(function($q) use ($year, $month) {
                    $q->whereYear('tanggal_mulai', $year)->whereMonth('tanggal_mulai', $month)
                      ->orWhereYear('tanggal_selesai', $year)->whereMonth('tanggal_selesai', $month);
                })
                ->get();

            // Hitung Alpa (Logika yang sama dengan Riwayat)
            $alpaCount = 0;
            $startOfMonth = now()->startOfMonth();
            $endOfMonth = now(); // Sampai hari ini saja

            for ($date = $startOfMonth->copy(); $date->lte($endOfMonth); $date->addDay()) {
                if ($date->isWeekend()) continue;

                $dateStr = $date->toDateString();
                $hasPresensi = $dataPresensi->where('tanggal', $dateStr)->isNotEmpty();
                if ($hasPresensi) continue;

                $hasIzin = $dataIzin->filter(fn($i) => $date->between($i->tanggal_mulai, $i->tanggal_selesai))->isNotEmpty();
                if ($hasIzin) continue;

                $alpaCount++;
            }

            $stats = [
                'hadir'       => $dataPresensi->count(),
                'tepat_waktu' => $dataPresensi->where('status_masuk', 'tepat_waktu')->count(),
                'terlambat'   => $dataPresensi->where('status_masuk', 'terlambat')->count(),
                'izin'        => $dataIzin->count(),
                'alpa'        => $alpaCount,
            ];
        }

        return view('shared.profil', compact('user', 'karyawan', 'qrCode', 'qrImage', 'stats'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();
        $karyawan = $user->karyawan;

        if (!$karyawan) {
            return back()->with('error', 'Data profil detail tidak ditemukan.');
        }

        $validated = $request->validate([
            'nama_lengkap'  => 'required|string|max:100',
            'nomor_telepon' => 'nullable|string|max:20',
            'foto'          => 'nullable|image|max:2048',
        ]);

        DB::transaction(function () use ($user, $karyawan, $validated, $request) {
            if ($request->hasFile('foto')) {
                if ($karyawan->foto) {
                    Storage::disk('public')->delete($karyawan->foto);
                }
                $validated['foto'] = $request->file('foto')->store("foto/{$user->id}", 'public');
            }

            $karyawan->update($validated);

            // Sinkronisasi dengan Pusat Bantuan jika role adalah Operator atau HRD
            if ($user->role === 'operator' || $user->role === 'hrd') {
                $bantuan = Bantuan::where('slug', $user->role)->first();
                if ($bantuan) {
                    $bantuan->update([
                        'nama'    => $validated['nama_lengkap'],
                        'telepon' => $validated['nomor_telepon'],
                        'whatsapp'=> $validated['nomor_telepon'] // Asumsi no telp = WA
                    ]);
                }
            }
        });

        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    /** Hapus foto profil */
    public function deleteFoto()
    {
        $user = auth()->user();
        $karyawan = $user->karyawan;

        if ($karyawan && $karyawan->foto) {
            Storage::disk('public')->delete($karyawan->foto);
            $karyawan->update(['foto' => null]);
            return back()->with('success', 'Foto profil berhasil dihapus.');
        }

        return back()->with('error', 'Foto profil tidak ditemukan.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'password_lama' => 'required|string',
            'password'      => 'required|string|min:8|confirmed',
        ], [
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
            'password.min' => 'Password minimal 8 karakter.'
        ]);

        $user = auth()->user();

        if (!Hash::check($request->password_lama, $user->password)) {
            return back()->with('error', 'Password lama tidak sesuai.');
        }

        $user->update(['password' => Hash::make($request->password)]);

        return back()->with('success', 'Password berhasil diubah.');
    }

    public function downloadQr()
    {
        $karyawan = auth()->user()->karyawan;
        if (!$karyawan) return abort(404);

        $qrCode = $karyawan->kode_karyawan ?? (string) $karyawan->id;
        $qrUrl  = route('karyawan.profile-public', ['kode_karyawan' => $qrCode]);

        $qrSvg = QrCode::format('svg')
            ->size(512)
            ->margin(2)
            ->errorCorrection('H')
            ->generate($qrUrl);

        return response($qrSvg, 200, [
            'Content-Type' => 'image/svg+xml',
        ]);
    }
}
