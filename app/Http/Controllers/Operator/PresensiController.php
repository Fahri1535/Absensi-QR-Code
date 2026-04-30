<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\{Karyawan, Presensi};
use Illuminate\Http\Request;

class PresensiController extends Controller
{
    public function index(Request $request)
    {
        // Filter
        $tanggal    = $request->input('tanggal', today()->toDateString());
        $karyawanId = $request->input('karyawan_id');
        $status     = $request->input('status');

        $query = Presensi::with('karyawan')
            ->whereDate('tanggal', $tanggal)
            ->when($karyawanId, fn($q) => $q->where('karyawan_id', $karyawanId))
            ->when($status, fn($q) => $q->where('status_masuk', $status))
            ->orderByDesc('jam_datang');

        $presensiList = $query->paginate(20)->withQueryString();

        // Statistik hari yang dipilih
        $totalHadir     = Presensi::whereDate('tanggal', $tanggal)->whereNotNull('jam_datang')->count();
        $totalTerlambat = Presensi::whereDate('tanggal', $tanggal)->where('status_masuk', 'terlambat')->count();
        $totalPulang    = Presensi::whereDate('tanggal', $tanggal)->whereNotNull('jam_pulang')->count();
        $totalKaryawan  = Karyawan::where('status', 'aktif')->count();

        $listKaryawan = Karyawan::where('status', 'aktif')->orderBy('nama_lengkap')->get();

        return view('operator.presensi', compact(
            'presensiList',
            'tanggal',
            'karyawanId',
            'status',
            'totalHadir',
            'totalTerlambat',
            'totalPulang',
            'totalKaryawan',
            'listKaryawan'
        ));
    }
}
