<?php

namespace App\Http\Controllers\Hrd;

use App\Http\Controllers\Controller;
use App\Models\{Izin, Karyawan, Presensi};

class DashboardController extends Controller
{
    public function index()
    {
        $totalKaryawan = Karyawan::where('status', 'aktif')
            ->whereHas('user', fn ($q) => $q->where('role', 'karyawan'))
            ->count();
        $izinPending   = Izin::where('status', 'pending')->count();

        $hadirHariIni = Presensi::whereDate('tanggal', today())
            ->whereNotNull('jam_datang')->count();

        $terlambatHariIni = Presensi::whereDate('tanggal', today())
            ->where('status_masuk', 'terlambat')->count();

        $tidakHadir = $totalKaryawan - $hadirHariIni;

        // Nama variable HARUS cocok dengan yang dipakai view
        $presensiHariIni = Presensi::with('karyawan')
            ->whereDate('tanggal', today())
            ->whereNotNull('jam_datang')
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
            'izinMenunggu'
        ));
    }
}
