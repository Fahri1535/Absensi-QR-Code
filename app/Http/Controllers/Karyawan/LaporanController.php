<?php

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use App\Models\{Presensi, Karyawan, Izin, JadwalKerja};
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\RiwayatPresensiExport;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $karyawan = auth()->user()->karyawan;
        $bulan = $request->input('bulan', now()->format('Y-m'));
        [$year, $month] = explode('-', $bulan);

        // 1. Ambil Data Presensi
        $dataPresensi = Presensi::with('karyawan.user')
            ->where('karyawan_id', $karyawan->id)
            ->whereYear('tanggal', $year)
            ->whereMonth('tanggal', $month)
            ->get();

        // 2. Ambil Data Izin yang disetujui
        $dataIzin = Izin::where('karyawan_id', $karyawan->id)
            ->where('status', 'disetujui')
            ->where(function($q) use ($year, $month) {
                $q->whereYear('tanggal_mulai', $year)->whereMonth('tanggal_mulai', $month)
                  ->orWhereYear('tanggal_selesai', $year)->whereMonth('tanggal_selesai', $month);
            })
            ->get();

        // 3. Transform & Deteksi Alpa
        $generatedData = new Collection();
        $startOfMonth = Carbon::create($year, $month, 1)->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();
        if ($endOfMonth->isFuture()) $endOfMonth = now();

        for ($date = $startOfMonth->copy(); $date->lte($endOfMonth); $date->addDay()) {
            if ($date->isWeekend()) continue;

            $dateStr = $date->toDateString();
            $presensi = $dataPresensi->where('tanggal', $dateStr)->first();
            if ($presensi) {
                $presensi->is_izin = false;
                $presensi->status = $presensi->status_masuk;
                $generatedData->push($presensi);
                continue;
            }

            $izin = $dataIzin->filter(fn($i) => $date->between($i->tanggal_mulai, $i->tanggal_selesai))->first();
            if ($izin) {
                $generatedData->push((object)[
                    'id' => 'izin-' . $izin->id . '-' . $date->format('Ymd'),
                    'karyawan_id' => $karyawan->id,
                    'karyawan' => $karyawan,
                    'tanggal' => $date->copy(),
                    'hari' => $date->translatedFormat('l'),
                    'jam_datang' => null,
                    'jam_pulang' => null,
                    'status_masuk' => $izin->jenis_izin,
                    'status_pulang' => $izin->jenis_izin,
                    'status' => $izin->jenis_izin,
                    'keterangan' => 'Izin: ' . $izin->keterangan,
                    'is_izin' => true
                ]);
                continue;
            }

            $generatedData->push((object)[
                'id' => 'alpa-' . $karyawan->id . '-' . $date->format('Ymd'),
                'karyawan_id' => $karyawan->id,
                'karyawan' => $karyawan,
                'tanggal' => $date->copy(),
                'hari' => $date->translatedFormat('l'),
                'jam_datang' => null,
                'jam_pulang' => null,
                'status_masuk' => 'alpa',
                'status_pulang' => 'alpa',
                'status' => 'alpa',
                'keterangan' => 'Tidak hadir tanpa keterangan',
                'is_izin' => false
            ]);
        }

        // 4. Sortir
        $laporanAll = $generatedData->sortByDesc('tanggal');

        // 5. Manual Pagination
        $perPage = 31;
        $page = $request->input('page', 1);

        // Filter status tambahan (jika ada)
        $statusFilter = $request->input('status');
        if ($statusFilter) {
            $laporanAll = $laporanAll->filter(function($item) use ($statusFilter) {
                return ($item->status ?? $item->status_masuk) === $statusFilter;
            });
        }

        $laporan = new \Illuminate\Pagination\LengthAwarePaginator(
            $laporanAll->forPage($page, $perPage),
            $laporanAll->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $listKaryawan = collect([$karyawan]);
        $karyawanId = $karyawan->id;

        $summary = [
            'total' => $laporanAll->count(),
            'tepat_waktu' => $laporanAll->where('status', 'tepat_waktu')->count(),
            'terlambat' => $laporanAll->where('status', 'terlambat')->count(),
            'pulang_awal' => $laporanAll->where('status_pulang', 'pulang_awal')->count(),
            'izin' => $laporanAll->where('is_izin', true)->count(),
            'alpa' => $laporanAll->where('status', 'alpa')->count(),
        ];

        return view('shared.laporan', compact(
            'laporan', 'listKaryawan', 'summary', 'bulan', 'karyawanId'
        ));
    }

    public function export(Request $request)
    {
        $karyawan = auth()->user()->karyawan;
        $bulan    = $request->input('bulan', now()->format('Y-m'));
        $status   = $request->input('status');
        $format   = $request->input('format', 'xlsx');

        $filename = "laporan-presensi-{$karyawan->nama_lengkap}-{$bulan}.{$format}";

        // Gunakan RiwayatPresensiExport karena isinya sama (data personal)
        $export = new RiwayatPresensiExport($karyawan->id, $bulan, $status);

        if ($format === 'pdf') {
            return Excel::download($export, $filename, \Maatwebsite\Excel\Excel::MPDF);
        }

        return Excel::download($export, $filename);
    }
}
