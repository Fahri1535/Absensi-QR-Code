<?php

namespace App\Exports;

use App\Models\Presensi;
use Maatwebsite\Excel\Concerns\{FromQuery, WithHeadings, WithMapping, WithTitle, ShouldAutoSize};

class LaporanPresensiExport implements FromQuery, WithHeadings, WithMapping, WithTitle, ShouldAutoSize
{
    public function __construct(
        private string  $bulan,
        private ?int    $karyawanId = null
    ) {}

    public function query()
    {
        [$year, $month] = explode('-', $this->bulan);

        return Presensi::with('karyawan')
            ->whereYear('tanggal', $year)
            ->whereMonth('tanggal', $month)
            ->when($this->karyawanId, fn($q) => $q->where('karyawan_id', $this->karyawanId))
            ->orderBy('tanggal')
            ->orderBy('karyawan_id');
    }

    public function headings(): array
    {
        return ['Nama Karyawan', 'Jabatan', 'Tanggal', 'Jam Masuk', 'Jam Pulang', 'Status Masuk', 'Status Pulang'];
    }

    public function map($row): array
    {
        return [
            $row->karyawan?->nama_lengkap ?? '—',
            $row->karyawan?->jabatan ?? '—',
            $row->tanggal->format('d/m/Y'),
            $row->jam_datang ?? '—',
            $row->jam_pulang ?? '—',
            ucfirst(str_replace('_', ' ', $row->status_masuk ?? '—')),
            ucfirst(str_replace('_', ' ', $row->status_pulang ?? '—')),
        ];
    }

    public function title(): string
    {
        return "Laporan {$this->bulan}";
    }
}
