<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Presensi extends Model
{
    protected $table = 'presensi';

    protected $fillable = [
        'karyawan_id', 'tanggal',
        'jam_datang', 'jam_pulang',
        'status_masuk', 'status_pulang',
    ];

    protected $casts = ['tanggal' => 'date'];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class);
    }

    public function getStatusMasukLabelAttribute(): string
    {
        return match($this->status_masuk) {
            'tepat_waktu' => 'Tepat Waktu',
            'terlambat'   => 'Terlambat',
            default       => '—',
        };
    }
}
