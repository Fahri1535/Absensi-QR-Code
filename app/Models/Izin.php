<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Izin extends Model
{
    protected $table = 'izin';

    protected $fillable = [
        'karyawan_id', 'jenis_izin',
        'tanggal_mulai', 'tanggal_selesai',
        'keterangan', 'lampiran',
        'status', 'catatan_hrd',
        'approved_by', 'approved_at',
    ];

    protected $casts = [
        'tanggal_mulai'   => 'date',
        'tanggal_selesai' => 'date',
        'approved_at'     => 'datetime',
    ];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getDurasiAttribute(): int
    {
        return $this->tanggal_mulai->diffInDays($this->tanggal_selesai) + 1;
    }

    public function getLampiranUrlAttribute(): ?string
    {
        return $this->lampiran ? asset('storage/' . $this->lampiran) : null;
    }
}
