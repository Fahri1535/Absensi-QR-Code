<?php

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use App\Models\Presensi;
use Illuminate\Http\Request;
use App\Models\Karyawan;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $karyawan = auth()->user()->karyawan;
        $bulan = $request->input('bulan', now()->format('Y-m'));
        [$year, $month] = explode('-', $bulan);

        $laporan = Presensi::with('karyawan.user')
            ->where('karyawan_id', $karyawan->id)
            ->whereYear('tanggal', $year)
            ->whereMonth('tanggal', $month)
            ->orderBy('tanggal')
            ->paginate(30)
            ->withQueryString();

        // Untuk view shared.laporan, kita butuh $listKaryawan tapi hanya berisi diri sendiri
        $listKaryawan = collect([$karyawan]);
        $karyawanId = $karyawan->id;

        $summary = Presensi::where('karyawan_id', $karyawan->id)
            ->whereYear('tanggal', $year)
            ->whereMonth('tanggal', $month)
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN status_masuk = "tepat_waktu" THEN 1 ELSE 0 END) as tepat_waktu,
                SUM(CASE WHEN status_masuk = "terlambat" THEN 1 ELSE 0 END) as terlambat,
                SUM(CASE WHEN status_pulang = "pulang_awal" THEN 1 ELSE 0 END) as pulang_awal
            ')
            ->first();

        // Convert object to array for shared.laporan view compatibility
        $summary = [
            'total' => $summary->total ?? 0,
            'tepat_waktu' => $summary->tepat_waktu ?? 0,
            'terlambat' => $summary->terlambat ?? 0,
            'pulang_awal' => $summary->pulang_awal ?? 0,
        ];

        return view('shared.laporan', compact(
            'laporan', 'listKaryawan', 'summary', 'bulan', 'karyawanId'
        ));
    }
}
