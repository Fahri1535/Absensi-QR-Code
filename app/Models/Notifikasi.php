<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notifikasi extends Model
{
    protected $table = 'notifikasi';

    protected $fillable = [
        'user_id', 'judul', 'pesan',
        'ikon', 'warna', 'link', 'is_read',
    ];

    protected $casts = ['is_read' => 'boolean'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /* ─── Factory helper ─────────────────────────────────────── */

    public static function kirim(
        User|int $user,
        string $judul,
        string $pesan,
        string $ikon = 'fa-bell',
        string $warna = 'teal',
        ?string $link = null
    ): self {
        $userId = $user instanceof User ? $user->id : $user;
        return static::create(compact('userId', 'judul', 'pesan', 'ikon', 'warna', 'link') + ['user_id' => $userId]);
    }
}
