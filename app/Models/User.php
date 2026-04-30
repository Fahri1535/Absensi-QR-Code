<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = ['username', 'password', 'role'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['password' => 'hashed'];
    }

    /* ─── Relations ─────────────────────────────────────────── */

    public function karyawan()
    {
        return $this->hasOne(Karyawan::class);
    }

    public function notifikasi()
    {
        return $this->hasMany(Notifikasi::class)->latest();
    }

    /* ─── Helpers ────────────────────────────────────────────── */

    public function getAuthIdentifierName(): string
    {
        return 'username';
    }

    public function isKaryawan(): bool { return $this->role === 'karyawan'; }
    public function isOperator(): bool { return $this->role === 'operator'; }
    public function isHrd(): bool      { return $this->role === 'hrd'; }
}
