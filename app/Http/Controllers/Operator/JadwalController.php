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
            'jam_masuk'          => 'required|date_format:H:i',
            'jam_pulang'         => 'required|date_format:H:i|after:jam_masuk',
            'toleransi_menit'    => 'required|integer|min:0|max:120',
            'hari_kerja'         => 'nullable|string|max:100',
            'kantor_latitude'    => 'nullable|numeric|between:-90,90',
            'kantor_longitude'   => 'nullable|numeric|between:-180,180',
            'radius_meter'       => 'nullable|integer|min:10|max:50000',
        ]);

        if (filled($validated['kantor_latitude'] ?? null) xor filled($validated['kantor_longitude'] ?? null)) {
            return back()->withErrors(['kantor_latitude' => 'Isi lintang dan bujur kantor bersamaan, atau kosongkan keduanya.'])->withInput();
        }

        if ((filled($validated['kantor_latitude'] ?? null) || filled($validated['radius_meter'] ?? null))
            && (! filled($validated['kantor_latitude'] ?? null) || ! filled($validated['radius_meter'] ?? null))) {
            return back()->withErrors(['radius_meter' => 'Untuk batas jarak: isi lintang, bujur, dan radius (meter).'])->withInput();
        }

        if (! filled($validated['kantor_latitude'] ?? null)) {
            $validated['kantor_latitude'] = null;
            $validated['kantor_longitude'] = null;
            $validated['radius_meter'] = null;
        }

        // FIXED: ::get() mengembalikan Collection bukan single model
        // Harus ::getSetting() untuk ambil satu record
        $jadwal = JadwalKerja::getSetting();
        $jadwal->update($validated);

        return back()->with('success', 'Jadwal kerja berhasil disimpan.');
    }
}