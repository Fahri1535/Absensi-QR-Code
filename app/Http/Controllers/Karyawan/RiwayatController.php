<?php

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use App\Models\Presensi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\RiwayatPresensiExport;

class RiwayatController extends Controller
{
    public function index(Request $request)
    {
        $karyawan = auth()->user()->karyawan;

        $bulan = $request->input('bulan', now()->format('Y-m'));
        [$year, $month] = explode('-', $bulan);

        $riwayat = Presensi::where('karyawan_id', $karyawan->id)
            ->whereYear('tanggal', $year)
            ->whereMonth('tanggal', $month)
            ->orderByDesc('tanggal')
            ->paginate(20);

        // Statistik bulan ini
        $stat = Presensi::where('karyawan_id', $karyawan->id)
            ->whereYear('tanggal', $year)
            ->whereMonth('tanggal', $month)
            ->selectRaw('
                COUNT(*) as total_hadir,
                SUM(CASE WHEN status_masuk = "terlambat" THEN 1 ELSE 0 END) as total_terlambat,
                SUM(CASE WHEN jam_pulang IS NULL THEN 1 ELSE 0 END) as total_tidak_pulang
            ')
            ->first();

        return view('karyawan.riwayat', compact('karyawan', 'riwayat', 'stat', 'bulan'));
    }

    public function export(Request $request)
    {
        $karyawan = auth()->user()->karyawan;
        $bulan    = $request->input('bulan', now()->format('Y-m'));

        return Excel::download(
            new RiwayatPresensiExport($karyawan->id, $bulan),
            "riwayat-presensi-{$bulan}.xlsx"
        );
    }
}
