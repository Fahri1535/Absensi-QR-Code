<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\{Izin, JadwalKerja, Karyawan, Presensi, QrCode};
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $jadwal = JadwalKerja::getSetting();
        $today = today();
        $todayStr = $today->toDateString();

        // Semua karyawan aktif (kecuali operator)
        $karyawanAktif = Karyawan::where('status', 'aktif')
            ->whereHas('user', fn($q) => $q->where('role', '!=', 'operator'))
            ->get();
        $totalKaryawan = $karyawanAktif->count();

        // Karyawan yang sudah presensi hari ini
        $presensiHariIniCollection = Presensi::with('karyawan')
            ->whereDate('tanggal', $todayStr)
            ->whereNotNull('jam_datang')
            ->whereHas('karyawan.user', fn($q) => $q->where('role', '!=', 'operator'))
            ->orderByDesc('jam_datang')
            ->get();
        $hadirHariIni = $presensiHariIniCollection->count();
        $sudahPresensiIds = $presensiHariIniCollection->pluck('karyawan_id')->unique()->toArray();

        // Hitung terlambat
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

        // Alpa = belum presensi, tapi hanya pada hari kerja (sama seperti page Data Presensi)
        $totalAlpa = $today->isWeekend() ? 0 : $belumPresensiList->count();
        $belumPresensi = $belumPresensiList->count();

        // Grafik 7 hari terakhir
        $grafik = collect(range(6, 0))->map(function ($i) use ($todayStr) {
            $date = Carbon::parse($todayStr)->subDays($i);
            $dateStr = $date->toDateString();

            // Hitung hadir
            $hadir = Presensi::whereDate('tanggal', $dateStr)
                ->whereNotNull('jam_datang')
                ->whereHas('karyawan.user', fn($q) => $q->where('role', '!=', 'operator'))
                ->count();

            // Hitung izin disetujui
            $izin = Izin::where('status', 'disetujui')
                ->where('tanggal_mulai', '<=', $date)
                ->where('tanggal_selesai', '>=', $date)
                ->whereHas('karyawan.user', fn($q) => $q->where('role', '!=', 'operator'))
                ->distinct('karyawan_id')
                ->count('karyawan_id');

            return [
                'tanggal' => $date->format('d/m'),
                'hadir'   => $hadir,
                'izin'    => $izin,
            ];
        });

        // Presensi terbaru hari ini (limit 5)
        $presensiTerkini = $presensiHariIniCollection->take(5);

        // Status QR
        $qrMasukAktif  = QrCode::where('tipe', 'masuk')->value('is_active') ?? false;
        $qrPulangAktif = QrCode::where('tipe', 'pulang')->value('is_active') ?? false;

        return view('operator.dashboard', compact(
            'jadwal',
            'totalKaryawan',
            'hadirHariIni',
            'terlambatHariIni',
            'totalAlpa',
            'belumPresensi',
            'jumlahIzinHariIni',
            'belumPresensiList',
            'grafik',
            'presensiTerkini',
            'qrMasukAktif',
            'qrPulangAktif'
        ));
    }
}
