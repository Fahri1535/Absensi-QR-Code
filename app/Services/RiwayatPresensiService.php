<?php

namespace App\Services;

use App\Models\{JadwalKerja, Karyawan, Presensi, Izin};
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Logika riwayat kehadiran — satu sumber untuk Laporan & Format Presensi.
 * Scope: user role karyawan/hrd (sama seperti Laporan Presensi operator).
 */
class RiwayatPresensiService
{
    /** Query karyawan yang punya akun karyawan atau HRD. */
    public static function karyawanQuery(bool $aktifOnly = true)
    {
        return Karyawan::whereHas('user', fn ($q) => $q->whereIn('role', ['karyawan', 'hrd']))
            ->when($aktifOnly, fn ($q) => $q->where('status', 'aktif'));
    }

    public static function karyawanIds(bool $aktifOnly = true): Collection
    {
        return self::karyawanQuery($aktifOnly)->pluck('id');
    }

    /**
     * Hitung statistik per hari kerja untuk rentang tanggal (inklusif).
     * Logika identik dengan Operator\LaporanController.
     */
    public static function statsForRange(
        Carbon $startDate,
        Carbon $endDate,
        ?Collection $listKaryawan = null,
        bool $aktifOnly = true
    ): array {
        $stats = [
            'hadir'     => 0,
            'terlambat' => 0,
            'alpha'     => 0,
            'sakit'     => 0,
            'lainnya'   => 0,
        ];

        $listKaryawan ??= self::karyawanQuery($aktifOnly)->get();

        if ($listKaryawan->isEmpty()) {
            return $stats;
        }

        $jadwal = JadwalKerja::getSetting();

        $karyawanIds = $listKaryawan->pluck('id');

        $dataPresensi = Presensi::whereIn('karyawan_id', $karyawanIds)
            ->whereDate('tanggal', '>=', $startDate->toDateString())
            ->whereDate('tanggal', '<=', $endDate->toDateString())
            ->get();

        $dataIzin = Izin::whereIn('karyawan_id', $karyawanIds)
            ->where('status', 'disetujui')
            ->where('tanggal_mulai', '<=', $endDate)
            ->where('tanggal_selesai', '>=', $startDate)
            ->get();

        $end = $endDate->copy();
        if ($end->isFuture()) {
            $end = now()->copy()->startOfDay();
        }

        for ($date = $startDate->copy(); $date->lte($end); $date->addDay()) {
            // Skip tanggal yang alpha-nya belum final. Untuk tanggal masa lalu
            // selalu final; untuk tanggal HARI INI baru final setelah jam masuk
            // terlewati; untuk tanggal masa depan tidak final. Mencegah bug
            // "semua alpha" pada tanggal hari ini saat jam masuk (mis. shift
            // malam 19:00) belum lewat. Weekend ikut tertangkap karena helper
            // juga mengembalikan false untuk weekend.
            if (! $jadwal->alphaFinalUntukTanggal($date)) {
                continue;
            }

            $dateStr = $date->toDateString();

            foreach ($listKaryawan as $karyawan) {
                $presensi = $dataPresensi->first(function ($p) use ($karyawan, $dateStr) {
                    $tgl = $p->tanggal instanceof Carbon
                        ? $p->tanggal->toDateString()
                        : (string) $p->tanggal;

                    return $p->karyawan_id == $karyawan->id && $tgl === $dateStr;
                });

                if ($presensi) {
                    if ($presensi->status_masuk === 'terlambat') {
                        $stats['terlambat']++;
                    } else {
                        // tepat_waktu atau null → hadir (ada baris presensi)
                        $stats['hadir']++;
                    }
                    continue;
                }

                $izin = $dataIzin->first(function ($i) use ($karyawan, $date) {
                    return $i->karyawan_id == $karyawan->id
                        && $date->between($i->tanggal_mulai, $i->tanggal_selesai);
                });

                if ($izin) {
                    if ($izin->jenis_izin === 'sakit') {
                        $stats['sakit']++;
                    } else {
                        $stats['lainnya']++;
                    }
                    continue;
                }

                $stats['alpha']++;
            }
        }

        return $stats;
    }

    /** Statistik all-time: jumlahkan per bulan (sama seperti laporan di setiap bulan). */
    public static function statsAllTime(bool $aktifOnly = true): array
    {
        $karyawanIds = self::karyawanIds($aktifOnly);

        if ($karyawanIds->isEmpty()) {
            return [
                'hadir' => 0, 'terlambat' => 0, 'alpha' => 0, 'sakit' => 0, 'lainnya' => 0,
            ];
        }

        $earliest = collect([
            Presensi::whereIn('karyawan_id', $karyawanIds)->min('tanggal'),
            Izin::whereIn('karyawan_id', $karyawanIds)->where('status', 'disetujui')->min('tanggal_mulai'),
        ])->filter()->min();

        if (! $earliest) {
            return [
                'hadir' => 0, 'terlambat' => 0, 'alpha' => 0, 'sakit' => 0, 'lainnya' => 0,
            ];
        }

        $totals = [
            'hadir' => 0, 'terlambat' => 0, 'alpha' => 0, 'sakit' => 0, 'lainnya' => 0,
        ];

        $listKaryawan = self::karyawanQuery($aktifOnly)->get();
        $startMonth   = Carbon::parse($earliest)->startOfMonth();
        $endMonth     = now()->startOfMonth();

        for ($month = $startMonth->copy(); $month->lte($endMonth); $month->addMonth()) {
            $start = Carbon::create($month->year, $month->month, 1)->startOfMonth();
            $end   = $start->copy()->endOfMonth();

            $monthStats = self::statsForRange($start, $end, $listKaryawan, $aktifOnly);

            foreach ($totals as $key => $val) {
                $totals[$key] += $monthStats[$key];
            }
        }

        return $totals;
    }

    /** Statistik satu bulan — untuk verifikasi hapus per bulan. */
    public static function statsForMonth(int $year, int $month, bool $aktifOnly = true): array
    {
        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end   = $start->copy()->endOfMonth();

        return self::statsForRange($start, $end, null, $aktifOnly);
    }

    /** Jumlah baris DB (presensi / izin) untuk scope karyawan+HRD. */
    public static function dbCounts(bool $aktifOnly = true): array
    {
        $ids = self::karyawanIds($aktifOnly);

        return [
            'presensi' => Presensi::whereIn('karyawan_id', $ids)->count(),
            'izin'     => Izin::whereIn('karyawan_id', $ids)->count(),
        ];
    }
}
