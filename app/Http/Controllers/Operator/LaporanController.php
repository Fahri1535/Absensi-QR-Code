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

        // 3. Get all karyawan to scan (all active karyawan & hrd)
        $listKaryawanToScan = $karyawanId 
            ? Karyawan::where('id', $karyawanId)->get() 
            : Karyawan::where('status', 'aktif')
                ->whereHas('user', function($q) {
                    $q->whereIn('role', ['karyawan', 'hrd']);
                })
                ->get();
        
        $generatedData = new Collection();
        $startOfMonth = Carbon::create($year, $month, 1)->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();
        if ($endOfMonth->isFuture()) $endOfMonth = now();

        // Generate data for each karyawan and each day
        foreach ($listKaryawanToScan as $karyawan) {
            for ($date = $startOfMonth->copy(); $date->lte($endOfMonth); $date->addDay()) {
                $dateStr = $date->toDateString();
                
                // Skip tanggal yang alpha-nya belum final. Untuk tanggal masa
                // lalu selalu final. Untuk tanggal HARI INI, baru final setelah
                // jam masuk terlewati. Untuk tanggal masa depan tidak final.
                // Ini mencegah "semua alpha" pada tanggal hari ini saat jam
                // masuk (mis. shift malam 19:00) belum lewat. Weekend ikut
                // tertangkap karena helper juga mengembalikan false untuk weekend.
                if (! $jadwal->alphaFinalUntukTanggal($date)) {
                    continue;
                }
                
                // Check for presensi
                $presensi = $dataPresensi->filter(fn($p) => $p->karyawan_id == $karyawan->id && $p->tanggal->toDateString() === $dateStr)->first();
                if ($presensi) {
                    $presensi->is_izin = false;
                    $presensi->is_alpa = false;
                    // Sudah absen masuk tapi belum absen pulang -> status Pending
                    $presensi->is_pending = empty($presensi->jam_pulang);
                    $presensi->status = $presensi->is_pending ? 'pending' : $presensi->status_masuk;
                    $generatedData->push($presensi);
                    continue;
                }

                // Check for izin
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
                        'is_alpa' => false,
                        'is_pending' => false
                    ]);
                    continue;
                }
                
                // If no presensi and no izin, mark as alpa
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
                    'is_izin' => false,
                    'is_alpa' => true,
                    'is_pending' => false
                ]);
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
                if ($statusFilter === 'izin') {
                    return $item->is_izin ?? false;
                }
                if ($statusFilter === 'pending') {
                    return $item->is_pending ?? false;
                }
                if ($statusFilter === 'pulang_awal') {
                    return ($item->status_pulang ?? null) === 'pulang_awal';
                }
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
            'total'       => $laporanAll->count(),
            'hadir'       => $laporanAll->where('is_izin', false)->where('is_alpa', false)->count(),
            'tepat_waktu' => $laporanAll->where('status', 'tepat_waktu')->count(),
            'terlambat'   => $laporanAll->where('status', 'terlambat')->count(),
            'pending'     => $laporanAll->where('is_pending', true)->count(),
            'pulang_awal' => $laporanAll->where('status_pulang', 'pulang_awal')->count(),
            'izin'        => $laporanAll->where('is_izin', true)->count(),
            'alpa'        => $laporanAll->where('is_alpa', true)->count(),
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
