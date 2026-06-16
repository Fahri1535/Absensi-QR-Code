<?php

namespace App\Exports;

use App\Models\{Presensi, Izin, Karyawan};
use Maatwebsite\Excel\Concerns\{FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize};
use Illuminate\Support\Collection;
use Carbon\Carbon;

class LaporanPresensiExport implements FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize
{
    public function __construct(
        private string  $bulan,
        private ?int    $karyawanId = null,
        private ?string $status     = null
    ) {}

    public function collection()
    {
        [$year, $month] = explode('-', $this->bulan);

        // 1. Ambil Data Presensi
        $dataPresensi = Presensi::with('karyawan')
            ->whereYear('tanggal', $year)
            ->whereMonth('tanggal', $month)
            ->when($this->karyawanId, fn($q) => $q->where('karyawan_id', $this->karyawanId))
            ->get();

        // 2. Ambil Data Izin
        $dataIzin = Izin::with('karyawan')
            ->where('status', 'disetujui')
            ->where(function($q) use ($year, $month) {
                $q->whereYear('tanggal_mulai', $year)->whereMonth('tanggal_mulai', $month)
                  ->orWhereYear('tanggal_selesai', $year)->whereMonth('tanggal_selesai', $month);
            })
            ->when($this->karyawanId, fn($q) => $q->where('karyawan_id', $this->karyawanId))
            ->get();

        // 3. Deteksi Alpa
        $listKaryawanToScan = $this->karyawanId 
            ? Karyawan::where('id', $this->karyawanId)->get() 
            : Karyawan::where('status', 'aktif')
                ->whereHas('user', fn($q) => $q->whereIn('role', ['karyawan', 'hrd']))
                ->get();

        $generatedData = new Collection();
        
        $startOfMonth = Carbon::create($year, $month, 1)->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();
        if ($endOfMonth->isFuture()) $endOfMonth = now();

        foreach ($listKaryawanToScan as $karyawan) {
            for ($date = $startOfMonth->copy(); $date->lte($endOfMonth); $date->addDay()) {
                $dateStr = $date->toDateString();
                
                // Skip weekend
                if ($date->isWeekend()) {
                    continue;
                }
                
                // Cek apakah ada presensi
                $presensi = $dataPresensi->filter(function($item) use ($karyawan, $dateStr) {
                    return $item->karyawan_id == $karyawan->id && 
                           ($item->tanggal instanceof \Carbon\Carbon ? $item->tanggal->toDateString() : $item->tanggal) == $dateStr;
                })->first();

                if ($presensi) {
                    $presensi->is_izin = false;
                    $presensi->is_alpa = false;
                    $presensi->status = $presensi->status_masuk;
                    $presensi->hari = $date->translatedFormat('l');
                    $generatedData->push($presensi);
                    continue;
                }

                $izin = $dataIzin->where('karyawan_id', $karyawan->id)
                                 ->filter(fn($i) => $date->between($i->tanggal_mulai, $i->tanggal_selesai))
                                 ->first();
                if ($izin) {
                    $generatedData->push((object)[
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

                // Alpa
                $generatedData->push((object)[
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
                    'is_alpa' => true
                ]);
            }
        }

        // Filter status jika ada
        if ($this->status) {
            $generatedData = $generatedData->filter(function($item) {
                if ($this->status === 'izin') {
                    return $item->is_izin ?? false;
                }
                if ($this->status === 'pulang_awal') {
                    return ($item->status_pulang ?? null) === 'pulang_awal';
                }
                return ($item->status ?? $item->status_masuk) === $this->status;
            });
        }

        return $generatedData->sortBy([
            ['tanggal', 'desc'],
            ['karyawan.nama_lengkap', 'asc']
        ]);
    }

    public function headings(): array
    {
        return ['Nama Karyawan', 'Tanggal', 'Hari', 'Jam Masuk', 'Jam Pulang', 'Durasi', 'Status', 'Keterangan'];
    }

    public function map($row): array
    {
        $tanggal = $row->tanggal instanceof Carbon ? $row->tanggal : Carbon::parse($row->tanggal);
        
        // Hitung durasi
        $durasi = '—';
        if ($row->jam_datang && $row->jam_pulang) {
            $durasi = Carbon::parse($row->jam_datang)->diff(Carbon::parse($row->jam_pulang))->format('%H:%I');
        }
        
        // Format status
        $status = $row->status ?? $row->status_masuk ?? '—';
        $statusLabel = ucfirst(str_replace('_', ' ', $status));
        
        return [
            $row->karyawan?->nama_lengkap ?? '—',
            $tanggal->format('d/m/Y'),
            $row->hari ?? $tanggal->translatedFormat('l'),
            $row->jam_datang ? Carbon::parse($row->jam_datang)->format('H:i') : '—',
            $row->jam_pulang ? Carbon::parse($row->jam_pulang)->format('H:i') : '—',
            $durasi,
            $statusLabel,
            $row->keterangan ?? '—',
        ];
    }

    public function title(): string
    {
        return "Laporan Presensi {$this->bulan}";
    }
}
