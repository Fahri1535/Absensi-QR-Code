<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class JadwalKerja extends Model
{
    protected $table = 'jadwal_kerja';

    protected $fillable = [
        'jam_masuk',
        'jam_pulang',
        'toleransi_menit',
        'masuk_lebih_awal_menit',
        'pulang_lebih_awal_menit',
        'durasi_scan_masuk_menit',
        'durasi_scan_pulang_menit',
        'hari_kerja',
        'kantor_latitude',
        'kantor_longitude',
        'radius_meter',
    ];

    /** Menit tambahan setelah jam masuk + toleransi — window scan masuk masih terbuka (fallback bila kolom belum ada). */
    public const MASUK_TUTUP_EXTRA_MENIT = 60;

    /** Menit tambahan setelah jam pulang — window scan pulang masih terbuka (fallback bila kolom belum ada). */
    public const PULANG_TUTUP_EXTRA_MENIT = 60;

    protected function casts(): array
    {
        return [
            'kantor_latitude' => 'float',
            'kantor_longitude' => 'float',
            'radius_meter' => 'integer',
        ];
    }

    // Ambil atau buat default (singleton)
    public static function getSetting(): self
    {
        return static::firstOrCreate([], [
            'jam_masuk'                => '08:00:00',
            'jam_pulang'               => '17:00:00',
            'toleransi_menit'          => 5,
            'masuk_lebih_awal_menit'   => 15,
            'pulang_lebih_awal_menit'  => 30,
            'durasi_scan_masuk_menit'  => self::MASUK_TUTUP_EXTRA_MENIT,
            'durasi_scan_pulang_menit' => self::PULANG_TUTUP_EXTRA_MENIT,
            'hari_kerja'               => 'Senin - Jumat',
        ]);
    }

    /** Window presensi masuk/pulang — satu sumber untuk QR, jadwal, dan scan API. */
    public function presensiWindows(?Carbon $onDate = null): array
    {
        $base = $onDate ? $onDate->copy()->startOfDay() : now()->startOfDay();

        $jamMasuk = $base->copy()->setTimeFromTimeString(
            Carbon::parse($this->jam_masuk)->format('H:i:s')
        );
        $jamPulang = $base->copy()->setTimeFromTimeString(
            Carbon::parse($this->jam_pulang)->format('H:i:s')
        );

        $tol = (int) ($this->toleransi_menit ?? 5);
        $awalMasuk = (int) ($this->masuk_lebih_awal_menit ?? 15);
        $awalPulang = (int) ($this->pulang_lebih_awal_menit ?? 30);
        // Durasi tutup scan (bisa diatur di halaman Jadwal Kerja); fallback ke konstanta bila kolom belum ada.
        $durasiMasuk = (int) ($this->durasi_scan_masuk_menit ?? self::MASUK_TUTUP_EXTRA_MENIT);
        $durasiPulang = (int) ($this->durasi_scan_pulang_menit ?? self::PULANG_TUTUP_EXTRA_MENIT);

        return [
            'masuk_buka'  => $jamMasuk->copy()->subMinutes($awalMasuk),
            'masuk_tutup' => $jamMasuk->copy()->addMinutes($tol + $durasiMasuk),
            'pulang_buka' => $jamPulang->copy()->subMinutes($awalPulang),
            'pulang_tutup'=> $jamPulang->copy()->addMinutes($durasiPulang),
            'jam_masuk'   => $jamMasuk,
            'jam_pulang'  => $jamPulang,
        ];
    }
}