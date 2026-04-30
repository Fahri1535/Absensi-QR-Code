<?php

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Hash, Storage};
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ProfilController extends Controller
{
    public function index()
{
    $karyawan = auth()->user()->karyawan;

    $qrCode = null;
    $qrImage = null;

    if ($karyawan) {
        $qrCode = $karyawan->kode_karyawan ?? $karyawan->id;

        $qrImage = QrCode::size(150)
            ->generate($qrCode);
    }

    return view('karyawan.profil', compact(
        'karyawan',
        'qrCode',
        'qrImage'
    ));
}

    public function update(Request $request)
    {
        $karyawan = auth()->user()->karyawan;

        $validated = $request->validate([
            'nama_lengkap'  => 'required|string|max:100',
            'jabatan'       => 'nullable|string|max:100',
            'nomor_telepon' => 'nullable|string|max:20',
            'foto'          => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('foto')) {
            if ($karyawan->foto) {
                Storage::disk('public')->delete($karyawan->foto);
            }
            $validated['foto'] = $request->file('foto')
                ->store("foto/{$karyawan->id}", 'public');
        }

        $karyawan->update($validated);

        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'password_lama' => 'required|string',
            'password'      => 'required|string|min:8|confirmed',
        ]);

        $user = auth()->user();

        if (! Hash::check($request->password_lama, $user->password)) {
            return back()->withErrors(['password_lama' => 'Password lama tidak sesuai.']);
        }

        $user->update(['password' => Hash::make($request->password)]);

        return back()->with('success', 'Password berhasil diubah.');
    }

    /** Download QR personal karyawan */
    public function downloadQr()
    {
        $karyawan = auth()->user()->karyawan;

        $qrImage = QrCode::format('png')
            ->size(400)
            ->errorCorrection('H')
            ->generate($karyawan->kode_karyawan ?? $karyawan->id);

        return response($qrImage, 200, [
            'Content-Type'        => 'image/png',
            'Content-Disposition' => "attachment; filename=\"qr-{$karyawan->id}.png\"",
        ]);
    }
}
