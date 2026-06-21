<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\{Presensi, Izin};
use App\Services\RiwayatPresensiService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SetupController extends Controller
{
    public function index(Request $request)
    {
        $dbConnection = config('database.default');
        $dbName = config("database.connections.{$dbConnection}.database");

        // Statistik & ringkasan: karyawan/HRD AKTIF — sama seperti Laporan Presensi
        $stats   = RiwayatPresensiService::statsAllTime(aktifOnly: true);
        $dbCounts = RiwayatPresensiService::dbCounts(aktifOnly: true);

        $ringkasan = [
            'hari_hadir'     => $stats['hadir'] + $stats['terlambat'],
            'hari_izin'      => $stats['sakit'] + $stats['lainnya'],
            'hari_kerja'     => $stats['hadir'] + $stats['terlambat'] + $stats['alpha'] + $stats['sakit'] + $stats['lainnya'],
            'baris_presensi' => $dbCounts['presensi'],
            'baris_izin'     => $dbCounts['izin'],
        ];

        return view('operator.setup', compact(
            'dbConnection',
            'dbName',
            'stats',
            'ringkasan'
        ));
    }

    /**
     * Hapus SEMUA riwayat presensi karyawan & HRD aktif (dan opsional izin).
     */
    public function deletePresensi(Request $request)
    {
        $validated = $request->validate([
            'scope' => 'required|in:presensi,semua',
        ], [
            'scope.required' => 'Pilih jenis data yang ingin dihapus.',
        ]);

        $karyawanIds = RiwayatPresensiService::karyawanIds(aktifOnly: true);

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
            ? "Semua data riwayat presensi ({$presensiTerhapus}) & izin ({$izinTerhapus}) karyawan/HRD aktif berhasil dihapus!"
            : "Semua data riwayat presensi ({$presensiTerhapus}) karyawan/HRD aktif berhasil dihapus (data izin tetap aman)!";

        return back()->with('success', $pesan);
    }

    /**
     * Hapus riwayat presensi karyawan & HRD aktif untuk BULAN tertentu.
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
        $year  = (int) $year;
        $month = (int) $month;

        $namaBulan = Carbon::createFromDate($year, $month, 1)
            ->locale('id')->translatedFormat('F Y');

        $karyawanIds = RiwayatPresensiService::karyawanIds(aktifOnly: true);

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        $presensiQuery = Presensi::whereIn('karyawan_id', $karyawanIds)
            ->whereYear('tanggal', $year)
            ->whereMonth('tanggal', $month);

        $presensiTerhapus = $presensiQuery->count();
        $presensiQuery->delete();

        $izinTerhapus = 0;
        if ($validated['scope'] === 'semua') {
            $awalBulan  = Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $akhirBulan = $awalBulan->copy()->endOfMonth();

            // Izin yang rentangnya bersinggungan dengan bulan ini (sama seperti laporan)
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
            ? "Data presensi ({$presensiTerhapus}) & izin ({$izinTerhapus}) karyawan/HRD aktif untuk bulan {$namaBulan} berhasil dihapus!"
            : "Data presensi ({$presensiTerhapus}) karyawan/HRD aktif untuk bulan {$namaBulan} berhasil dihapus (data izin tetap aman)!";

        return back()->with('success', $pesan);
    }
}
