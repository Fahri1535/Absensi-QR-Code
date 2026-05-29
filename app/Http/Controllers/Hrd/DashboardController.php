<?php

namespace App\Http\Controllers\Hrd;

use App\Http\Controllers\Controller;
use App\Models\{Izin, Karyawan, Presensi, JadwalKerja};

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $karyawanPribadi = $user->karyawan;

        // Jika HRD belum punya record karyawan, buatkan default agar tidak error (sama seperti di PresensiController)
        if (!$karyawanPribadi) {
            $karyawanPribadi = Karyawan::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'nama_lengkap' => ucfirst($user->role) . ' Admin',
                    'status' => 'aktif',
                    'kode_karyawan' => 'ADM-' . \Illuminate\Support\Str::upper(\Illuminate\Support\Str::random(12)),
                ]
            );
        }

        $totalKaryawan = Karyawan::where('status', 'aktif')
            ->whereHas('user', fn ($q) => $q->where('role', 'karyawan'))
            ->count();
        $izinPending   = Izin::where('status', 'pending')->count();

        $hadirHariIni = Presensi::whereDate('tanggal', today())
            ->whereNotNull('jam_datang')
            ->whereHas('karyawan.user', fn($q) => $q->where('role', 'karyawan'))
            ->count();

        $terlambatHariIni = Presensi::whereDate('tanggal', today())
            ->where('status_masuk', 'terlambat')
            ->whereHas('karyawan.user', fn($q) => $q->where('role', 'karyawan'))
            ->count();

        $tidakHadir = max(0, $totalKaryawan - $hadirHariIni);

        // Jadwal & Presensi Pribadi untuk Widget Dashboard
        $jadwal = JadwalKerja::getSetting();
        $presensiPribadi = Presensi::where('karyawan_id', $karyawanPribadi->id)
            ->whereDate('tanggal', today())
            ->first();

        // Nama variable HARUS cocok dengan yang dipakai view
        $presensiHariIni = Presensi::with('karyawan')
            ->whereDate('tanggal', today())
            ->whereNotNull('jam_datang')
            ->whereHas('karyawan.user', fn($q) => $q->where('role', 'karyawan'))
            ->orderByDesc('jam_datang')
            ->take(10)
            ->get();

        $izinMenunggu = Izin::with('karyawan')
            ->where('status', 'pending')
            ->orderByDesc('created_at')
            ->take(5)
            ->get();

        return view('hrd.dashboard', compact(
            'totalKaryawan',
            'izinPending',
            'hadirHariIni',
            'terlambatHariIni',
            'tidakHadir',
            'presensiHariIni',
            'izinMenunggu',
            'jadwal',
            'presensiPribadi'
        ));
    }
}
