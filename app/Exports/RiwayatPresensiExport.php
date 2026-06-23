<?php

namespace App\Exports;

use App\Models\{Presensi, Izin, Karyawan, JadwalKerja};
use Maatwebsite\Excel\Concerns\{FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize};
use Illuminate\Support\Collection;
use Carbon\Carbon;

class RiwayatPresensiExport implements FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize
{
    public function __construct(
        private int     $karyawanId,
        private string  $bulan,        // format: Y-m
        private ?string $status = null
    ) {}

    public function collection()
    {
        [$year, $month] = explode('-', $this->bulan);
        $karyawan = Karyawan::find($this->karyawanId);

        // 1. Ambil Data Presensi
        $dataPresensi = Presensi::where('karyawan_id', $this->karyawanId)
            ->whereYear('tanggal', $year)
            ->whereMonth('tanggal', $month)
            ->get();

        // 2. Ambil Data Izin
        $dataIzin = Izin::where('karyawan_id', $this->karyawanId)
            ->where('status', 'disetujui')
            ->where(function($q) use ($year, $month) {
                $q->whereYear('tanggal_mulai', $year)->whereMonth('tanggal_mulai', $month)
                  ->orWhereYear('tanggal_selesai', $year)->whereMonth('tanggal_selesai', $month);
            })
            ->get();

        // 3. Deteksi Alpa
        $generatedData = new Collection();
        $jadwal = JadwalKerja::getSetting();
        $startOfMonth = Carbon::create($year, $month, 1)->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();
        if ($endOfMonth->isFuture()) $endOfMonth = now();

        for ($date = $startOfMonth->copy(); $date->lte($endOfMonth); $date->addDay()) {
            // Skip tanggal yang alpha-nya belum final (weekend atau jam masuk
            // tanggal tsb. belum lewat). Konsisten dengan riwayat di halaman —
            // mencegah "semua alpha" untuk tanggal hari ini yang jam masuknya
            // (mis. shift malam) belum lewat.
            if (! $jadwal->alphaFinalUntukTanggal($date)) continue;

            $dateStr = $date->toDateString();
            $presensi = $dataPresensi->filter(function($item) use ($dateStr) {
                return ($item->tanggal instanceof \Carbon\Carbon ? $item->tanggal->toDateString() : $item->tanggal) == $dateStr;
            })->first();
            if ($presensi) {
                $presensi->karyawan = $karyawan; // Pastikan relasi ada untuk mapping
                $presensi->status_label = $presensi->status_masuk;
                $generatedData->push($presensi);
                continue;
            }

            $izin = $dataIzin->filter(fn($i) => $date->between($i->tanggal_mulai, $i->tanggal_selesai))->first();
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

            $generatedData->push((object)[
                'karyawan' => $karyawan,
                'tanggal' => $date->copy(),
                'jam_datang' => null,
                'jam_pulang' => null,
                'status_masuk' => 'alpa',
                'status_pulang' => 'alpa',
                'status_label' => 'alpa'
            ]);
        }

        // Filter status jika ada
        if ($this->status) {
            $status = $this->status;
            $generatedData = $generatedData->filter(function($item) use ($status) {
                $statusVal = $item->status_label ?? ($item->status_masuk ?? null);
                return $statusVal === $status;
            });
        }

        return $generatedData->sortBy('tanggal');
    }

    public function headings(): array
    {
        return ['Nama Karyawan', 'Tanggal', 'Jam Masuk', 'Jam Pulang', 'Status Masuk', 'Status Pulang'];
    }

    public function map($row): array
    {
        $tanggal = $row->tanggal instanceof Carbon ? $row->tanggal : Carbon::parse($row->tanggal);

        return [
            $row->karyawan?->nama_lengkap ?? '—',
            $tanggal->format('d/m/Y'),
            $row->jam_datang ? Carbon::parse($row->jam_datang)->format('H:i') : '—',
            $row->jam_pulang ? Carbon::parse($row->jam_pulang)->format('H:i') : '—',
            ucfirst(str_replace('_', ' ', $row->status_masuk ?? '—')),
            ucfirst(str_replace('_', ' ', $row->status_pulang ?? '—')),
        ];
    }

    public function title(): string
    {
        return "Riwayat {$this->bulan}";
    }
}
