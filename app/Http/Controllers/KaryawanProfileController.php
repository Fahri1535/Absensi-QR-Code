<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use Illuminate\Http\Request;

class KaryawanProfileController extends Controller
{
    public function show($kode_karyawan)
    {
        $karyawan = Karyawan::where('kode_karyawan', $kode_karyawan)->firstOrFail();
        
        return view('karyawan.profile-public', compact('karyawan'));
    }
}
