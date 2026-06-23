<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\{Karyawan, Presensi, Izin, JadwalKerja};
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class PresensiController extends Controller
{
    public function index(Request $request)
    {
        // Filter
        $tanggal    = $request->input('tanggal', today()->toDateString());
        $karyawanId = $request->input('karyawan_id');
        $status     = $request->input('status'); // tepat_waktu, terlambat, alpa, atau jenis_izin

        $dateObj = Carbon::parse($tanggal);

        // 1. Ambil Data Presensi Fisik
        $queryPresensi = Presensi::with('karyawan')
            ->whereDate('tanggal', $tanggal)
            ->whereHas('karyawan.user', function($q) {
                $q->where('role', '!=', 'operator');
            })
            ->when($karyawanId, fn($q) => $q->where('karyawan_id', $karyawanId));

        $dataPresensi = $queryPresensi->get();

        // 2. Ambil Data Izin yang disetujui pada tanggal tersebut
        $queryIzin = Izin::with('karyawan')
            ->where('status', 'disetujui')
            ->whereDate('tanggal_mulai', '<=', $tanggal)
            ->whereDate('tanggal_selesai', '>=', $tanggal)
            ->whereHas('karyawan.user', function($q) {
                $q->where('role', '!=', 'operator');
            })
            ->when($karyawanId, fn($q) => $q->where('karyawan_id', $karyawanId));

        $dataIzin = $queryIzin->get();

        // 3. Gabungkan dan Deteksi Alpa (Berdasarkan Hari Kerja)
        $listKaryawanToScan = $karyawanId 
            ? Karyawan::where('id', $karyawanId)->get() 
            : Karyawan::where('status', 'aktif')
                ->whereHas('user', function($q) {
                    $q->where('role', '!=', 'operator');
                })
                ->get();
        $generatedData = new Collection();
        $isWeekend = $dateObj->isWeekend();

        foreach ($listKaryawanToScan as $karyawan) {
            // Cek presensi fisik
            $presensi = $dataPresensi->where('karyawan_id', $karyawan->id)->first();
            if ($presensi) {
                $presensi->is_izin = false;
                $presensi->is_alpa = false;
                // Sudah absen masuk tapi belum absen pulang -> status Pending
                $presensi->is_pending = empty($presensi->jam_pulang);
                $presensi->display_status = $presensi->is_pending ? 'pending' : $presensi->status_masuk;
                $generatedData->push($presensi);
                continue;
            }

            // Cek izin
            $izin = $dataIzin->where('karyawan_id', $karyawan->id)->first();
            if ($izin) {
                $generatedData->push((object)[
                    'id' => 'izin-' . $izin->id,
                    'karyawan_id' => $karyawan->id,
                    'karyawan' => $karyawan,
                    'tanggal' => $dateObj->copy(),
                    'jam_datang' => null,
                    'jam_pulang' => null,
                    'status_masuk' => $izin->jenis_izin,
                    'status_pulang' => $izin->jenis_izin,
                    'display_status' => $izin->jenis_izin,
                    'keterangan' => 'Izin: ' . $izin->keterangan,
                    'is_izin' => true,
                    'is_alpa' => false,
                    'is_pending' => false
                ]);
                continue;
            }

            // Jika hari kerja tapi tidak ada data, maka Alpa
            if (!$isWeekend) {
                $generatedData->push((object)[
                    'id' => 'alpa-' . $karyawan->id,
                    'karyawan_id' => $karyawan->id,
                    'karyawan' => $karyawan,
                    'tanggal' => $dateObj->copy(),
                    'jam_datang' => null,
                    'jam_pulang' => null,
                    'status_masuk' => 'alpa',
                    'status_pulang' => 'alpa',
                    'display_status' => 'alpa',
                    'keterangan' => 'Tidak hadir tanpa keterangan',
                    'is_izin' => false,
                    'is_alpa' => true,
                    'is_pending' => false
                ]);
            }
        }

        // Filter by status jika dipilih
        if ($status) {
            $generatedData = $generatedData->filter(fn($item) => $item->display_status === $status);
        }

        // Sortir
        $allPresensi = $generatedData->sortBy('karyawan.nama_lengkap');

        // Pagination Manual
        $perPage = 20;
        $page = $request->input('page', 1);
        $presensiList = new \Illuminate\Pagination\LengthAwarePaginator(
            $allPresensi->forPage($page, $perPage),
            $allPresensi->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // Statistik
        $totalKaryawan  = Karyawan::where('status', 'aktif')
            ->whereHas('user', function($query) {
                $query->where('role', '!=', 'operator');
            })
            ->count();
        $totalHadir     = $allPresensi->where('is_izin', false)->where('is_alpa', false)->count();
        $totalTerlambat = $allPresensi->where('display_status', 'terlambat')->count();
        $totalIzin      = $allPresensi->where('is_izin', true)->count();
        $totalAlpa      = $allPresensi->where('is_alpa', true)->count();
        $totalPending   = $allPresensi->where('is_pending', true)->count();

        $listKaryawan = Karyawan::where('status', 'aktif')
            ->whereHas('user', function($query) {
                $query->where('role', '!=', 'operator');
            })
            ->orderBy('nama_lengkap')
            ->get();

        return view('operator.presensi', compact(
            'presensiList',
            'tanggal',
            'karyawanId',
            'status',
            'totalHadir',
            'totalTerlambat',
            'totalIzin',
            'totalAlpa',
            'totalPending',
            'totalKaryawan',
            'listKaryawan'
        ));
    }
}
