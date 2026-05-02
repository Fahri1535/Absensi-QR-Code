<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JadwalKerja extends Model
{
    protected $table = 'jadwal_kerja';

    protected $fillable = [
        'jam_masuk',
        'jam_pulang',
        'toleransi_menit',
        'hari_kerja',
        'kantor_latitude',
        'kantor_longitude',
        'radius_meter',
    ];

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
            'jam_masuk'       => '08:00:00',
            'jam_pulang'      => '17:00:00',
            'toleransi_menit' => 5,
            'hari_kerja'      => 'Senin - Jumat',
        ]);
    }
}