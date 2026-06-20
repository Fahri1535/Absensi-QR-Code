<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\{Presensi, Izin};
use Carbon\Carbon;

class SetupController extends Controller
{
    public function index(Request $request)
    {
        $dbConnection = config('database.default');
        $dbName = config("database.connections.{$dbConnection}.database");

        $presensiCount = Presensi::count();
        $izinCount     = Izin::count();

        // Statistik ringkas per-status (transparan buat operator)
        $statPresensi = [
            'tepat_waktu' => Presensi::where('status_masuk', 'tepat_waktu')->count(),
            'terlambat'   => Presensi::where('status_masuk', 'terlambat')->count(),
            'lainnya'     => Presensi::whereNull('status_masuk')
                                ->orWhereNotIn('status_masuk', ['tepat_waktu', 'terlambat'])
                                ->count(),
        ];

        $statIzin = [
            'izin'   => Izin::where('jenis_izin', 'izin')->count(),
            'sakit'  => Izin::where('jenis_izin', 'sakit')->count(),
            'cuti'   => Izin::where('jenis_izin', 'cuti')->count(),
            'lainnya'=> Izin::whereNotIn('jenis_izin', ['izin', 'sakit', 'cuti'])->count(),
        ];

        return view('operator.setup', compact(
            'dbConnection',
            'dbName',
            'presensiCount',
            'izinCount',
            'statPresensi',
            'statIzin'
        ));
    }

    /**
     * Hapus SEMUA riwayat presensi (dan opsional izin).
     *
     * scope:
     *  - 'presensi' : hapus tabel presensi saja
     *  - 'semua'    : hapus tabel presensi + izin
     */
    public function deletePresensi(Request $request)
    {
        $validated = $request->validate([
            'scope' => 'required|in:presensi,semua',
        ], [
            'scope.required' => 'Pilih jenis data yang ingin dihapus.',
        ]);

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        Presensi::truncate();

        $izinDihapus = false;
        if ($validated['scope'] === 'semua') {
            // Hapus file lampiran izin sebelum truncate (jaga-jaga)
            foreach (Izin::whereNotNull('lampiran')->get(['lampiran']) as $izin) {
                if ($izin->lampiran) {
                    \Storage::disk('public')->delete($izin->lampiran);
                }
            }
            Izin::truncate();
            $izinDihapus = true;
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $pesan = $izinDihapus
            ? 'Semua data riwayat presensi DAN pengajuan izin berhasil dihapus!'
            : 'Semua data riwayat presensi berhasil dihapus (data izin tetap aman)!';

        return back()->with('success', $pesan);
    }

    /**
     * Hapus riwayat presensi (dan opsional izin) untuk BULAN tertentu.
     *
     * Input:
     *  - bulan : format YYYY-MM (mis. 2026-06)
     *  - scope : 'presensi' | 'semua'
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

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // Hapus presensi pada bulan tsb
        $presensiTerhapus = Presensi::whereYear('tanggal', $year)
            ->whereMonth('tanggal', $month)
            ->count();
        Presensi::whereYear('tanggal', $year)
            ->whereMonth('tanggal', $month)
            ->delete();

        // Hapus izin yang mencakup bulan tsb (opsional)
        $izinTerhapus = 0;
        if ($validated['scope'] === 'semua') {
            $izinQuery = Izin::where(function ($q) use ($year, $month) {
                // Izin yang rentangnya (mulai..selesai) bersinggungan dengan bulan tsb
                $awalBulan = Carbon::createFromDate((int) $year, (int) $month, 1)->startOfMonth();
                $akhirBulan = $awalBulan->copy()->endOfMonth();

                $q->where('tanggal_mulai', '<=', $akhirBulan)
                  ->where('tanggal_selesai', '>=', $awalBulan);
            })->get();

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
            ? "Data presensi ({$presensiTerhapus}) & izin ({$izinTerhapus}) untuk bulan {$namaBulan} berhasil dihapus!"
            : "Data presensi ({$presensiTerhapus}) untuk bulan {$namaBulan} berhasil dihapus (data izin tetap aman)!";

        return back()->with('success', $pesan);
    }
}
