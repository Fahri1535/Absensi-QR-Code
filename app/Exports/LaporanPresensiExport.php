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
                
                // Cek apakah ada presensi
                $presensi = $dataPresensi->filter(function($item) use ($karyawan, $dateStr) {
                    return $item->karyawan_id == $karyawan->id && 
                           ($item->tanggal instanceof \Carbon\Carbon ? $item->tanggal->toDateString() : $item->tanggal) == $dateStr;
                })->first();

                if ($presensi) {
                    $presensi->status_label = $presensi->status_masuk;
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
                        'jam_datang' => null,
                        'jam_pulang' => null,
                        'status_masuk' => $izin->jenis_izin,
                        'status_pulang' => $izin->jenis_izin,
                        'status_label' => $izin->jenis_izin
                    ]);
                    continue;
                }

                // Baris Alpa dihilangkan agar laporan hanya menampilkan yang benar-benar presensi atau izin
            }
        }

        // Filter status jika ada
        if ($this->status) {
            $generatedData = $generatedData->filter(function($item) {
                $statusVal = $item->status_label ?? ($item->status_masuk ?? null);
                return $statusVal === $this->status;
            });
        }

        return $generatedData->sortBy([
            ['tanggal', 'asc'],
            ['karyawan.nama_lengkap', 'asc']
        ]);
    }

    public function headings(): array
    {
        return ['Nama Karyawan', 'Jabatan', 'Tanggal', 'Jam Masuk', 'Jam Pulang', 'Status Masuk', 'Status Pulang'];
    }

    public function map($row): array
    {
        $tanggal = $row->tanggal instanceof Carbon ? $row->tanggal : Carbon::parse($row->tanggal);
        
        return [
            $row->karyawan?->nama_lengkap ?? '—',
            $row->karyawan?->jabatan ?? '—',
            $tanggal->format('d/m/Y'),
            $row->jam_datang ? Carbon::parse($row->jam_datang)->format('H:i') : '—',
            $row->jam_pulang ? Carbon::parse($row->jam_pulang)->format('H:i') : '—',
            ucfirst(str_replace('_', ' ', $row->status_masuk ?? '—')),
            ucfirst(str_replace('_', ' ', $row->status_pulang ?? '—')),
        ];
    }

    public function title(): string
    {
        return "Laporan {$this->bulan}";
    }
}
