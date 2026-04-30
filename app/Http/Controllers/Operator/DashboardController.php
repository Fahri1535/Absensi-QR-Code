<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\{JadwalKerja, Karyawan, Presensi, QrCode};
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // FIXED: ::getSetting() bukan ::get()
        $jadwal = JadwalKerja::getSetting();

        $totalKaryawan = Karyawan::where('status', 'aktif')->count();

        $today = today()->toDateString();

        $hadirHariIni = Presensi::whereDate('tanggal', $today)
            ->whereNotNull('jam_datang')
            ->count();

        $terlambatHariIni = Presensi::whereDate('tanggal', $today)
            ->where('status_masuk', 'terlambat')
            ->count();

        $belumPresensi = $totalKaryawan - $hadirHariIni;

        // Grafik 7 hari terakhir
        $grafik = collect(range(6, 0))->map(function ($i) {
            $date = Carbon::today()->subDays($i);
            return [
                'tanggal' => $date->format('d/m'),
                'hadir'   => Presensi::whereDate('tanggal', $date)
                    ->whereNotNull('jam_datang')
                    ->count(),
            ];
        });

        // Presensi terbaru hari ini
        $presensiTerkini = Presensi::with('karyawan')
            ->whereDate('tanggal', $today)
            ->whereNotNull('jam_datang')
            ->orderByDesc('jam_datang')
            ->take(5)
            ->get();

        // FIXED: ambil status QR dari database bukan hardcode
        $qrMasukAktif  = QrCode::where('tipe', 'masuk')->value('is_active') ?? false;
        $qrPulangAktif = QrCode::where('tipe', 'pulang')->value('is_active') ?? false;

        return view('operator.dashboard', compact(
            'jadwal',
            'totalKaryawan',
            'hadirHariIni',
            'terlambatHariIni',
            'belumPresensi',
            'grafik',
            'presensiTerkini',
            'qrMasukAktif',
            'qrPulangAktif'
        ));
    }
}