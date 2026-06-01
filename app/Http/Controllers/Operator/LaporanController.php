<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\{Karyawan, Presensi, Izin, JadwalKerja};
use App\Exports\LaporanPresensiExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $bulan = $request->input('bulan', now()->format('Y-m'));
        
        if (strpos($bulan, '-') === false) {
            $bulan = now()->format('Y-m');
        }
        
        [$year, $month] = explode('-', $bulan);

        $karyawanId = $request->input('karyawan_id');
        $jadwal = JadwalKerja::getSetting();

        // 1. Ambil Data Presensi
        $queryPresensi = Presensi::with('karyawan')
            ->whereYear('tanggal', $year)
            ->whereMonth('tanggal', $month);

        if ($karyawanId) {
            $queryPresensi->where('karyawan_id', $karyawanId);
        }

        $dataPresensi = $queryPresensi->get();

        // 2. Ambil Data Izin yang disetujui
        $queryIzin = Izin::with('karyawan')
            ->where('status', 'disetujui')
            ->where(function($q) use ($year, $month) {
                $q->whereYear('tanggal_mulai', $year)->whereMonth('tanggal_mulai', $month)
                  ->orWhereYear('tanggal_selesai', $year)->whereMonth('tanggal_selesai', $month);
            });

        if ($karyawanId) {
            $queryIzin->where('karyawan_id', $karyawanId);
        }

        $dataIzin = $queryIzin->get();

        // 3. Transform Izin & Deteksi Alpa (Berdasarkan Hari Kerja)
        // HRD juga disertakan dalam laporan karena sekarang bisa melakukan presensi mandiri
        $listKaryawanToScan = $karyawanId 
            ? Karyawan::where('id', $karyawanId)->get() 
            : Karyawan::where('status', 'aktif')
                ->whereHas('user', fn($q) => $q->whereIn('role', ['karyawan', 'hrd']))
                ->get();
        
        $generatedData = new Collection();
        
        $startOfMonth = Carbon::create($year, $month, 1)->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();
        // Jangan scan melampaui hari ini
        if ($endOfMonth->isFuture()) {
            $endOfMonth = now();
        }

        foreach ($listKaryawanToScan as $karyawan) {
            for ($date = $startOfMonth->copy(); $date->lte($endOfMonth); $date->addDay()) {
                $dateStr = $date->toDateString();
                
                // Cek apakah ada presensi (Selalu tampilkan jika ada data, meskipun hari libur/weekend)
                $presensi = $dataPresensi->filter(function($item) use ($karyawan, $dateStr) {
                    return $item->karyawan_id == $karyawan->id && 
                           ($item->tanggal instanceof \Carbon\Carbon ? $item->tanggal->toDateString() : $item->tanggal) == $dateStr;
                })->first();

                if ($presensi) {
                    $presensi->is_izin = false;
                    $presensi->status = $presensi->status_masuk;
                    $generatedData->push($presensi);
                    continue;
                }

                // Cek apakah ada izin
                $izin = $dataIzin->where('karyawan_id', $karyawan->id)
                                 ->filter(fn($i) => $date->between($i->tanggal_mulai, $i->tanggal_selesai))
                                 ->first();
                
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
                        'is_izin' => true,
                        'is_alpa' => false
                    ]);
                    continue;
                }

                // Baris Alpa dihilangkan agar laporan hanya menampilkan yang benar-benar presensi atau izin
            }
        }

        // 4. Sortir
        $laporanAll = $generatedData->sortBy([
            ['tanggal', 'desc'],
            ['karyawan.nama_lengkap', 'asc']
        ]);

        // 5. Manual Pagination
        $perPage = 30;
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

        $listKaryawan = Karyawan::where('status', 'aktif')
            ->whereHas('user', function($q) {
                $q->whereIn('role', ['karyawan', 'hrd']);
            })
            ->orderBy('nama_lengkap')
            ->get();

        $summary = [
            'total' => $laporanAll->count(),
            'tepat_waktu' => $laporanAll->where('status', 'tepat_waktu')->count(),
            'terlambat' => $laporanAll->where('status', 'terlambat')->count(),
            'pulang_awal' => $laporanAll->where('status_pulang', 'lebih_awal')->count(),
            'izin' => $laporanAll->where('is_izin', true)->count(),
            'alpa' => $laporanAll->where('status', 'alpa')->count(),
        ];

        return view('shared.laporan', compact(
            'laporan', 'listKaryawan', 'summary', 'bulan', 'karyawanId'
        ));
    }

    public function export(Request $request)
    {
        $bulan      = $request->input('bulan', now()->format('Y-m'));
        $karyawanId = $request->input('karyawan_id');
        $status     = $request->input('status');
        $format     = $request->input('format', 'xlsx');

        $filename = "laporan-presensi-{$bulan}.{$format}";
        $export = new LaporanPresensiExport($bulan, $karyawanId, $status);

        if ($format === 'pdf') {
            return Excel::download($export, $filename, \Maatwebsite\Excel\Excel::MPDF);
        }

        return Excel::download($export, $filename);
    }
}
