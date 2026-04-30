<?php

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use App\Models\{Izin, JadwalKerja, Presensi};
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $karyawan        = auth()->user()->karyawan;
        // FIXED: pakai JadwalKerja::getSetting() bukan ::get()
        $jadwal          = JadwalKerja::getSetting();

        $presensiHariIni = Presensi::where('karyawan_id', $karyawan->id)
            ->whereDate('tanggal', today())
            ->first();

        // Statistik bulan ini
        // FIXED: kolom status_masuk → sesuai migration
        $bulanIni = Carbon::now()->startOfMonth();
        $stat = Presensi::where('karyawan_id', $karyawan->id)
            ->whereDate('tanggal', '>=', $bulanIni)
            ->selectRaw('
                COUNT(*) as total_hadir,
                SUM(CASE WHEN status_masuk = "terlambat" THEN 1 ELSE 0 END) as total_terlambat,
                SUM(CASE WHEN jam_pulang IS NOT NULL THEN 1 ELSE 0 END) as total_lengkap
            ')
            ->first();

        // Riwayat 7 hari terakhir
        $riwayatTerakhir = Presensi::where('karyawan_id', $karyawan->id)
            ->orderByDesc('tanggal')
            ->take(7)
            ->get();

        // Izin pending
        $izinPending = $karyawan->izin()
            ->where('status', 'pending')
            ->count();

        return view('karyawan.dashboard', compact(
            'karyawan', 'jadwal', 'presensiHariIni',
            'stat', 'riwayatTerakhir', 'izinPending'
        ));
    }
}