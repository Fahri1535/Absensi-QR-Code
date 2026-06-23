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
        $jadwal          = JadwalKerja::getSetting();

        $presensiHariIni = Presensi::where('karyawan_id', $karyawan->id)
            ->whereDate('tanggal', today())
            ->first();

        // Statistik bulan ini with proper calculations
        $bulanIni = Carbon::now()->startOfMonth();
        $presensiBulanIni = Presensi::where('karyawan_id', $karyawan->id)
            ->whereDate('tanggal', '>=', $bulanIni)
            ->get();
        
        // Get izin for this month too
        $izinBulanIni = Izin::where('karyawan_id', $karyawan->id)
            ->where('status', 'disetujui')
            ->where(function($q) use ($bulanIni) {
                $q->whereYear('tanggal_mulai', $bulanIni->year)
                  ->whereMonth('tanggal_mulai', $bulanIni->month)
                  ->orWhereYear('tanggal_selesai', $bulanIni->year)
                  ->whereMonth('tanggal_selesai', $bulanIni->month);
            })
            ->get();

        // Calculate stats properly
        $statsHadir = $presensiBulanIni->count();
        $statsTerlambat = $presensiBulanIni->where('status_masuk', 'terlambat')->count();
        
        // Count izin days
        $statsIzin = 0;
        $start = $bulanIni->copy();
        $end = $bulanIni->copy()->endOfMonth();
        if ($end->isFuture()) $end = Carbon::now();
        
        $statsAlpha = 0;
        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            // Skip tanggal yang alpha-nya belum final (weekend atau jam masuk
            // tanggal tsb. belum lewat). Konsisten dengan tempat lain — cegah
            // "semua alpha" untuk tanggal hari ini yang jam masuknya (mis. shift
            // malam) belum lewat.
            if (! $jadwal->alphaFinalUntukTanggal($date)) {
                continue;
            }
            $hasPresensi = $presensiBulanIni->filter(fn($p) => $p->tanggal->toDateString() === $date->toDateString())->count() > 0;
            if ($hasPresensi) continue;

            $hasIzin = $izinBulanIni->filter(fn($i) => $date->between($i->tanggal_mulai, $i->tanggal_selesai))->count() > 0;
            if ($hasIzin) {
                $statsIzin++;
            } else {
                $statsAlpha++;
            }
        }

        // Riwayat 7 hari terakhir with presensi + izin + alpa
        $riwayatTerbaru = collect();
        $sevenDaysAgo = Carbon::now()->subDays(7);
        
        for ($date = $sevenDaysAgo->copy(); $date->lte(Carbon::now()); $date->addDay()) {
            // Skip tanggal yang alpha-nya belum final — sama seperti statsAlpha.
            if (! $jadwal->alphaFinalUntukTanggal($date)) continue;
            
            $dateStr = $date->toDateString();
            
            // Check presensi
            $presensi = $presensiBulanIni->filter(fn($p) => $p->tanggal->toDateString() === $dateStr)->first();
            if ($presensi) {
                $presensi->is_izin = false;
                $presensi->is_alpa = false;
                $presensi->status = $presensi->status_masuk;
                $riwayatTerbaru->push($presensi);
                continue;
            }
            
            // Check izin
            $izin = $izinBulanIni->filter(fn($i) => $date->between($i->tanggal_mulai, $i->tanggal_selesai))->first();
            if ($izin) {
                $riwayatTerbaru->push((object)[
                    'id' => 'izin-' . $izin->id . '-' . $date->format('Ymd'),
                    'tanggal' => $date->copy(),
                    'jam_datang' => null,
                    'jam_pulang' => null,
                    'status_masuk' => $izin->jenis_izin,
                    'status_pulang' => $izin->jenis_izin,
                    'status' => $izin->jenis_izin,
                    'is_izin' => true,
                    'is_alpa' => false
                ]);
                continue;
            }
            
            // Alpa
            $riwayatTerbaru->push((object)[
                'id' => 'alpa-' . $karyawan->id . '-' . $date->format('Ymd'),
                'tanggal' => $date->copy(),
                'jam_datang' => null,
                'jam_pulang' => null,
                'status_masuk' => 'alpa',
                'status_pulang' => 'alpa',
                'status' => 'alpa',
                'is_izin' => false,
                'is_alpa' => true
            ]);
        }
        
        $riwayatTerbaru = $riwayatTerbaru->sortByDesc('tanggal');
        
        // Get izin terbaru
        $izinTerbaru = $karyawan->izin()->latest()->take(4)->get();

        // Izin pending
        $izinPending = $karyawan->izin()
            ->where('status', 'pending')
            ->count();

        return view('karyawan.dashboard', compact(
            'karyawan', 'jadwal', 'presensiHariIni',
            'statsHadir', 'statsTerlambat', 'statsIzin', 'statsAlpha',
            'riwayatTerbaru', 'izinTerbaru', 'izinPending'
        ));
    }
}
