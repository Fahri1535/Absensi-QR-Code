<?php

namespace App\Http\Controllers\Hrd;

use App\Http\Controllers\Controller;
use App\Models\{Karyawan, Presensi};
use App\Exports\LaporanPresensiExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $bulan = $request->input('bulan', now()->format('Y-m'));
        [$year, $month] = explode('-', $bulan);

        $karyawanId = $request->input('karyawan_id');

        $query = Presensi::with('karyawan')
            ->whereYear('tanggal', $year)
            ->whereMonth('tanggal', $month);

        if ($karyawanId) {
            $query->where('karyawan_id', $karyawanId);
        }

        $laporan = $query->orderBy('tanggal')->paginate(30)->withQueryString();

        // FIXED: nama variable $karyawanAll → $listKaryawan (sesuai view shared/laporan)
        $listKaryawan = Karyawan::where('status', 'aktif')->orderBy('nama_lengkap')->get();

        $summary = Presensi::whereYear('tanggal', $year)
            ->whereMonth('tanggal', $month)
            ->selectRaw('
                COUNT(*) as total_presensi,
                SUM(CASE WHEN status_masuk = "terlambat" THEN 1 ELSE 0 END) as total_terlambat
            ')
            ->first();

        return view('shared.laporan', compact(
            'laporan', 'listKaryawan', 'summary', 'bulan', 'karyawanId'
        ));
    }

    public function export(Request $request)
    {
        $bulan      = $request->input('bulan', now()->format('Y-m'));
        $karyawanId = $request->input('karyawan_id');

        return Excel::download(
            new LaporanPresensiExport($bulan, $karyawanId),
            "laporan-presensi-{$bulan}.xlsx"
        );
    }
}