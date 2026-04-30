<?php

namespace App\Http\Controllers\Hrd;

use App\Http\Controllers\Controller;
use App\Models\{Izin, Karyawan, Presensi};

class DashboardController extends Controller
{
    public function index()
    {
        // FIXED: variable name sesuai view ($totalKaryawan bukan $totalKary)
        $totalKaryawan = Karyawan::where('status', 'aktif')->count();
        $izinPending   = Izin::where('status', 'pending')->count();

        // FIXED: tambah $hadirHariIni dan $tidakHadir yang dipakai view
        $hadirHariIni = Presensi::whereDate('tanggal', today())
            ->whereNotNull('jam_datang')
            ->count();

        $tidakHadir = $totalKaryawan - $hadirHariIni;

        // Presensi hari ini untuk tabel
        // FIXED: pakai $presensiHariIni sesuai nama variable di view
        $presensiHariIni = Presensi::with('karyawan')
            ->whereDate('tanggal', today())
            ->whereNotNull('jam_datang')
            ->orderByDesc('jam_datang')
            ->take(10)
            ->get();

        // Izin pending untuk side panel
        // FIXED: pakai $izinMenunggu sesuai nama variable di view
        $izinMenunggu = Izin::with('karyawan')
            ->where('status', 'pending')
            ->orderByDesc('created_at')
            ->take(5)
            ->get();

        return view('hrd.dashboard', compact(
            'totalKaryawan',
            'izinPending',
            'hadirHariIni',
            'tidakHadir',
            'presensiHariIni',
            'izinMenunggu'
        ));
    }
}