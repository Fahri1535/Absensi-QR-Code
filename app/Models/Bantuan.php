<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bantuan extends Model
{
    protected $fillable = ['slug', 'nama', 'deskripsi', 'telepon', 'whatsapp', 'email'];
}
