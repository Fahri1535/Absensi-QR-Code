<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Karyawan extends Model
{
    protected $table = 'karyawan';

    protected $fillable = [
        'user_id', 'nama_lengkap', 'jabatan',
        'nomor_telepon', 'foto', 'status', 'kode_karyawan',
    ];

    /* ─── Relations ─────────────────────────────────────────── */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function presensi()
    {
        return $this->hasMany(Presensi::class);
    }

    public function izin()
    {
        return $this->hasMany(Izin::class);
    }

    /* ─── Helpers ────────────────────────────────────────────── */

    /** Presensi hari ini */
    public function presensiHariIni()
    {
        return $this->hasOne(Presensi::class)
            ->whereDate('tanggal', today());
    }

    public function getFotoUrlAttribute(): string
    {
        return $this->foto
            ? asset('storage/' . $this->foto)
            : 'https://ui-avatars.com/api/?name=' . urlencode($this->nama_lengkap) . '&background=0D1B2A&color=00C9A7';
    }
}
