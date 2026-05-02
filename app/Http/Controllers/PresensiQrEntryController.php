<?php

namespace App\Http\Controllers;

use App\Models\QrCode;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Tautan yang di-encode di QR fisik. Tamu → login → presensi.
 */
class PresensiQrEntryController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $token = $request->query('t');

        if (! $token || ! QrCode::where('kode_qr', $token)->where('is_active', true)->exists()) {
            $request->session()->forget('presensi_qr_token');

            return redirect()
                ->route('login')
                ->with('error', 'QR Code presensi tidak valid atau sudah dinonaktifkan.');
        }

        if (Auth::check()) {
            if (Auth::user()->role !== 'karyawan') {
                $home = match (Auth::user()->role) {
                    'operator' => route('operator.dashboard'),
                    'hrd' => route('hrd.dashboard'),
                    default => route('login'),
                };

                return redirect()->to($home)
                    ->with('error', 'Tautan presensi QR hanya untuk akun karyawan.');
            }

            $request->session()->forget('presensi_qr_token');

            return redirect()->route('karyawan.presensi', ['t' => $token]);
        }

        /*
         * Simpan token di sesi sebagai cadangan: redirect()->guest() menyimpan url.intended,
         * tetapi beberapa peramban/Google Lens bisa kehilangan query string atau sesi pertama.
         */
        $request->session()->put('presensi_qr_token', $token);

        return redirect()
            ->guest(route('login'))
            ->with('info', 'Silakan masuk terlebih dahulu untuk melanjutkan presensi dari QR.');
    }
}
