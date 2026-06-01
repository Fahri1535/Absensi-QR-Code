<?php

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use App\Models\{Presensi, Izin};
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\RiwayatPresensiExport;
use Illuminate\Support\Collection;

class RiwayatController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $karyawan = $user->karyawan;

        if (!$karyawan) {
            // Jika admin/hrd belum punya record karyawan, buatkan default agar tidak error
            $karyawan = \App\Models\Karyawan::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'nama_lengkap' => ucfirst($user->role) . ' Admin',
                    'status' => 'aktif',
                    'kode_karyawan' => 'ADM-' . \Illuminate\Support\Str::upper(\Illuminate\Support\Str::random(12)),
                ]
            );
        }

        $statusFilter = $request->input('status');

        // Mendukung format bulan (Y-m) atau dipisah bulan & tahun
        if ($request->has('bulan') && $request->has('tahun')) {
            $month = str_pad($request->bulan, 2, '0', STR_PAD_LEFT);
            $year = $request->tahun;
            $bulan = "{$year}-{$month}";
        } else {
            $bulan = $request->input('bulan', now()->format('Y-m'));
            if (strpos($bulan, '-') !== false) {
                [$year, $month] = explode('-', $bulan);
            } else {
                $month = str_pad($bulan, 2, '0', STR_PAD_LEFT);
                $year = $request->input('tahun', now()->year);
                $bulan = "{$year}-{$month}";
            }
        }

        // 1. Ambil Data Presensi
        $dataPresensi = Presensi::where('karyawan_id', $karyawan->id)
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
            
            // Cek apakah ada presensi
            $presensi = $dataPresensi->filter(function($item) use ($dateStr) {
                return ($item->tanggal instanceof \Carbon\Carbon ? $item->tanggal->toDateString() : $item->tanggal) == $dateStr;
            })->first();

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

        // 4. Hitung Statistik bulan ini (Summary) sebelum filter (Agar summary tetap akurat)
        $summary = [
            'total' => $generatedData->count(),
            'tepat_waktu' => $generatedData->where('status', 'tepat_waktu')->count(),
            'terlambat' => $generatedData->where('status', 'terlambat')->count(),
            'pulang_awal' => $generatedData->where('status_pulang', 'pulang_awal')->count(),
            'izin' => $generatedData->where('is_izin', true)->count(),
            'alpa' => $generatedData->where('status', 'alpa')->count(),
        ];

        // 5. Filter status jika ada (Fitur Laporan)
        if ($statusFilter) {
            $generatedData = $generatedData->filter(function($item) use ($statusFilter) {
                if ($statusFilter === 'izin') {
                    return $item->is_izin ?? false;
                }
                if ($statusFilter === 'pulang_awal') {
                    return ($item->status_pulang ?? null) === 'pulang_awal';
                }
                return $item->status === $statusFilter;
            });
        }

        // 6. Gabungkan dan Sortir
        $riwayatAll = $generatedData->sortByDesc('tanggal');

        // 7. Manual Pagination
        $perPage = 20;
        $page = $request->input('page', 1);
        $riwayat = new \Illuminate\Pagination\LengthAwarePaginator(
            $riwayatAll->forPage($page, $perPage),
            $riwayatAll->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('karyawan.riwayat', compact('karyawan', 'riwayat', 'summary', 'bulan'));
    }

    public function export(Request $request)
    {
        $karyawan = auth()->user()->karyawan;
        $format   = $request->input('format', 'xlsx');
        $status   = $request->input('status');
        
        if ($request->has('bulan') && $request->has('tahun')) {
            $month = str_pad($request->bulan, 2, '0', STR_PAD_LEFT);
            $year  = $request->tahun;
            $bulan = "{$year}-{$month}";
        } else {
            $bulan = $request->input('bulan', now()->format('Y-m'));
            // Jika format bulan salah (misal hanya angka bulan), perbaiki
            if (!str_contains($bulan, '-')) {
                $bulan = now()->year . '-' . str_pad($bulan, 2, '0', STR_PAD_LEFT);
            }
        }

        $filename = "laporan-riwayat-presensi-{$bulan}.{$format}";
        $export   = new RiwayatPresensiExport($karyawan->id, $bulan, $status);

        if ($format === 'pdf') {
            return Excel::download($export, $filename, \Maatwebsite\Excel\Excel::MPDF);
        }

        return Excel::download($export, $filename);
    }
}
