<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\{Karyawan, Presensi, Izin};
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SetupController extends Controller
{
    public function index(Request $request)
    {
        $dbConnection = config('database.default');
        $dbName = config("database.connections.{$dbConnection}.database");

        $karyawanIds = $this->karyawanHrdIds();

        $presensiCount = Presensi::whereIn('karyawan_id', $karyawanIds)->count();
        $izinCount     = Izin::whereIn('karyawan_id', $karyawanIds)->count();

        $stats = $this->computeRiwayatStats();

        return view('operator.setup', compact(
            'dbConnection',
            'dbName',
            'presensiCount',
            'izinCount',
            'stats'
        ));
    }

    /**
     * Hapus SEMUA riwayat presensi karyawan & HRD (dan opsional izin).
     *
     * scope:
     *  - 'presensi' : hapus presensi saja
     *  - 'semua'    : hapus presensi + izin
     */
    public function deletePresensi(Request $request)
    {
        $validated = $request->validate([
            'scope' => 'required|in:presensi,semua',
        ], [
            'scope.required' => 'Pilih jenis data yang ingin dihapus.',
        ]);

        $karyawanIds = $this->karyawanHrdIds();

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        $presensiTerhapus = Presensi::whereIn('karyawan_id', $karyawanIds)->count();
        Presensi::whereIn('karyawan_id', $karyawanIds)->delete();

        $izinTerhapus = 0;
        if ($validated['scope'] === 'semua') {
            $izinList = Izin::whereIn('karyawan_id', $karyawanIds)->get(['id', 'lampiran']);
            foreach ($izinList as $izin) {
                if ($izin->lampiran) {
                    \Storage::disk('public')->delete($izin->lampiran);
                }
            }
            $izinTerhapus = $izinList->count();
            Izin::whereIn('karyawan_id', $karyawanIds)->delete();
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $pesan = $validated['scope'] === 'semua'
            ? "Semua data riwayat presensi ({$presensiTerhapus}) & izin ({$izinTerhapus}) karyawan/HRD berhasil dihapus!"
            : "Semua data riwayat presensi ({$presensiTerhapus}) karyawan/HRD berhasil dihapus (data izin tetap aman)!";

        return back()->with('success', $pesan);
    }

    /**
     * Hapus riwayat presensi karyawan & HRD (dan opsional izin) untuk BULAN tertentu.
     */
    public function deletePresensiBulan(Request $request)
    {
        $validated = $request->validate([
            'bulan' => 'required|date_format:Y-m',
            'scope' => 'required|in:presensi,semua',
        ], [
            'bulan.required'     => 'Pilih bulan yang ingin dihapus.',
            'bulan.date_format'  => 'Format bulan tidak valid.',
            'scope.required'     => 'Pilih jenis data yang ingin dihapus.',
        ]);

        [$year, $month] = explode('-', $validated['bulan']);
        $namaBulan = Carbon::createFromDate((int) $year, (int) $month, 1)
            ->locale('id')->translatedFormat('F Y');

        $karyawanIds = $this->karyawanHrdIds();

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        $presensiQuery = Presensi::whereIn('karyawan_id', $karyawanIds)
            ->whereYear('tanggal', $year)
            ->whereMonth('tanggal', $month);

        $presensiTerhapus = $presensiQuery->count();
        $presensiQuery->delete();

        $izinTerhapus = 0;
        if ($validated['scope'] === 'semua') {
            $awalBulan  = Carbon::createFromDate((int) $year, (int) $month, 1)->startOfMonth();
            $akhirBulan = $awalBulan->copy()->endOfMonth();

            $izinQuery = Izin::whereIn('karyawan_id', $karyawanIds)
                ->where('tanggal_mulai', '<=', $akhirBulan)
                ->where('tanggal_selesai', '>=', $awalBulan)
                ->get();

            foreach ($izinQuery as $izin) {
                if ($izin->lampiran) {
                    \Storage::disk('public')->delete($izin->lampiran);
                }
            }
            $izinTerhapus = $izinQuery->count();
            Izin::whereIn('id', $izinQuery->pluck('id'))->delete();
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $pesan = $validated['scope'] === 'semua'
            ? "Data presensi ({$presensiTerhapus}) & izin ({$izinTerhapus}) karyawan/HRD untuk bulan {$namaBulan} berhasil dihapus!"
            : "Data presensi ({$presensiTerhapus}) karyawan/HRD untuk bulan {$namaBulan} berhasil dihapus (data izin tetap aman)!";

        return back()->with('success', $pesan);
    }

    /** ID karyawan yang user-nya berrole karyawan atau HRD. */
    protected function karyawanHrdIds(): Collection
    {
        return Karyawan::whereHas('user', fn ($q) => $q->whereIn('role', ['karyawan', 'hrd']))
            ->pluck('id');
    }

    /**
     * Statistik riwayat kehadiran — logika sama dengan Laporan Presensi:
     * Hadir, Terlambat, Alpha (dihitung), Sakit, Lainnya (semua izin kecuali sakit).
     */
    protected function computeRiwayatStats(): array
    {
        $karyawanIds  = $this->karyawanHrdIds();
        $listKaryawan = Karyawan::whereIn('id', $karyawanIds)->get();

        $stats = [
            'hadir'     => 0,
            'terlambat' => 0,
            'alpha'     => 0,
            'sakit'     => 0,
            'lainnya'   => 0,
        ];

        if ($listKaryawan->isEmpty()) {
            return $stats;
        }

        $dataPresensi = Presensi::whereIn('karyawan_id', $karyawanIds)->get();
        $dataIzin     = Izin::whereIn('karyawan_id', $karyawanIds)
            ->where('status', 'disetujui')
            ->get();

        // Rentang waktu: dari data terlama sampai hari ini (per bulan, sama seperti laporan)
        $earliest = collect([
            $dataPresensi->min('tanggal'),
            $dataIzin->min('tanggal_mulai'),
        ])->filter()->min();

        if (! $earliest) {
            return $stats;
        }

        $startMonth = Carbon::parse($earliest)->startOfMonth();
        $endMonth   = now()->startOfMonth();

        for ($month = $startMonth->copy(); $month->lte($endMonth); $month->addMonth()) {
            $year  = $month->year;
            $mon   = $month->month;
            $start = Carbon::create($year, $mon, 1)->startOfMonth();
            $end   = $start->copy()->endOfMonth();
            if ($end->isFuture()) {
                $end = now()->copy()->startOfDay();
            }

            for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
                if ($date->isWeekend()) {
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
                        if ($presensi->status_masuk === 'tepat_waktu') {
                            $stats['hadir']++;
                        } elseif ($presensi->status_masuk === 'terlambat') {
                            $stats['terlambat']++;
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
        }

        return $stats;
    }
}
