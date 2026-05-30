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

        // Statistik bulan ini (SIMPEL, TANPA KOLOM YANG RENTAN ERROR)
        $bulanIni = Carbon::now()->startOfMonth();
        $presensiBulanIni = Presensi::where('karyawan_id', $karyawan->id)
            ->whereDate('tanggal', '>=', $bulanIni)
            ->get();

        $stat = (object)[
            'total_hadir' => $presensiBulanIni->count(),
            'total_terlambat' => 0,
            'total_lengkap' => $presensiBulanIni->whereNotNull('jam_pulang')->count()
        ];

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