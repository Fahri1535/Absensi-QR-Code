<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Karyawan extends Model
{
    protected $table = 'karyawan';

    protected $fillable = [
        'user_id', 'nama_lengkap', 'jabatan',
        'nomor_telepon', 'foto', 'status', 'kode_karyawan',
        'jam_masuk', 'jam_pulang', 'toleransi_menit',
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

    /**
     * Get the employee's schedule, falling back to global if not set.
     */
    public function getScheduleAttribute(): \App\Models\JadwalKerja
    {
        $global = \App\Models\JadwalKerja::getSetting();
        
        // Create a new JadwalKerja instance with the merged data
        $schedule = new \App\Models\JadwalKerja();
        $schedule->jam_masuk = $this->jam_masuk ?? $global->jam_masuk;
        $schedule->jam_pulang = $this->jam_pulang ?? $global->jam_pulang;
        $schedule->toleransi_menit = $this->toleransi_menit ?? $global->toleransi_menit;
        $schedule->kantor_latitude = $global->kantor_latitude;
        $schedule->kantor_longitude = $global->kantor_longitude;
        $schedule->radius_meter = $global->radius_meter;
        $schedule->hari_kerja = $global->hari_kerja;
        
        return $schedule;
    }
}
