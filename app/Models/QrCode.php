<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class QrCode extends Model
{
    protected $table = 'qr_codes';
    protected $fillable = ['tipe', 'kode_qr', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];

    public static function getOrCreate(string $tipe): self
    {
        return static::firstOrCreate(
            ['tipe' => $tipe],
            ['kode_qr' => 'QR-' . strtoupper($tipe) . '-' . Str::random(32), 'is_active' => true]
        );
    }
}
