<?php

namespace App\Exports;

use App\Models\Presensi;
use Maatwebsite\Excel\Concerns\{FromQuery, WithHeadings, WithMapping, WithTitle, ShouldAutoSize};

class RiwayatPresensiExport implements FromQuery, WithHeadings, WithMapping, WithTitle, ShouldAutoSize
{
    public function __construct(
        private int    $karyawanId,
        private string $bulan        // format: Y-m
    ) {}

    public function query()
    {
        [$year, $month] = explode('-', $this->bulan);

        return Presensi::where('karyawan_id', $this->karyawanId)
            ->whereYear('tanggal', $year)
            ->whereMonth('tanggal', $month)
            ->orderBy('tanggal');
    }

    public function headings(): array
    {
        return ['Tanggal', 'Jam Masuk', 'Jam Pulang', 'Status Masuk', 'Status Pulang'];
    }

    public function map($row): array
    {
        return [
            $row->tanggal->format('d/m/Y'),
            $row->jam_datang ?? '—',
            $row->jam_pulang ?? '—',
            ucfirst(str_replace('_', ' ', $row->status_masuk ?? '—')),
            ucfirst(str_replace('_', ' ', $row->status_pulang ?? '—')),
        ];
    }

    public function title(): string
    {
        return "Riwayat {$this->bulan}";
    }
}
