<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\JadwalKerja;
use Illuminate\Http\Request;

class JadwalController extends Controller
{
    public function index()
    {
        // Redirect ke halaman QR Code yang memuat jadwal
        return redirect()->route('operator.qrcode');
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'jam_masuk'       => 'required|date_format:H:i',
            'jam_pulang'      => 'required|date_format:H:i|after:jam_masuk',
            'toleransi_menit' => 'required|integer|min:0|max:120',
            'hari_kerja'      => 'nullable|string|max:100',
        ]);

        // FIXED: ::get() mengembalikan Collection bukan single model
        // Harus ::getSetting() untuk ambil satu record
        $jadwal = JadwalKerja::getSetting();
        $jadwal->update($validated);

        return back()->with('success', 'Jadwal kerja berhasil disimpan.');
    }
}