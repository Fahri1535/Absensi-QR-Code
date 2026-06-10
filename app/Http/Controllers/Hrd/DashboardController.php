<?php

namespace App\Http\Controllers\Hrd;

use App\Http\Controllers\Controller;
use App\Models\{Izin, Karyawan, Presensi, JadwalKerja};
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $karyawanPribadi = $user->karyawan;

        // Jika HRD belum punya record karyawan, buatkan default
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

        $today = today();

        // Semua karyawan aktif (role karyawan)
        $karyawanAktif = Karyawan::where('status', 'aktif')
            ->whereHas('user', fn ($q) => $q->where('role', 'karyawan'))
            ->get();
        $totalKaryawan = $karyawanAktif->count();

        $izinPending = Izin::where('status', 'pending')->count();

        // Karyawan yang sudah presensi hari ini
        $presensiHariIniCollection = Presensi::with('karyawan')
            ->whereDate('tanggal', $today)
            ->whereNotNull('jam_datang')
            ->whereHas('karyawan.user', fn($q) => $q->where('role', 'karyawan'))
            ->orderByDesc('jam_datang')
            ->get();
        $hadirHariIni = $presensiHariIniCollection->count();
        $sudahPresensiIds = $presensiHariIniCollection->pluck('karyawan_id')->unique()->toArray();

        // Hitung terlambat hari ini
        $terlambatHariIni = $presensiHariIniCollection->where('status_masuk', 'terlambat')->count();

        // Karyawan dengan izin disetujui yang mencakup hari ini
        $izinHariIniIds = Izin::where('status', 'disetujui')
            ->where('tanggal_mulai', '<=', $today)
            ->where('tanggal_selesai', '>=', $today)
            ->pluck('karyawan_id')
            ->unique()
            ->toArray();
        $jumlahIzinHariIni = count($izinHariIniIds);

        // Belum presensi = belum hadir DAN tidak sedang izin
        $belumPresensiList = $karyawanAktif->filter(function ($k) use ($sudahPresensiIds, $izinHariIniIds) {
            return !in_array($k->id, $sudahPresensiIds)
                && !in_array($k->id, $izinHariIniIds);
        })->values();
        $belumPresensi = $belumPresensiList->count();

        // Jadwal & Presensi Pribadi
        $jadwal = JadwalKerja::getSetting();
        $presensiPribadi = Presensi::where('karyawan_id', $karyawanPribadi->id)
            ->whereDate('tanggal', $today)
            ->first();

        // Presensi terbaru (limit 10)
        $presensiTerbaru = $presensiHariIniCollection->take(10);

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
            'belumPresensi',
            'jumlahIzinHariIni',
            'belumPresensiList',
            'presensiTerbaru',
            'izinMenunggu',
            'jadwal',
            'presensiPribadi'
        ));
    }
}
