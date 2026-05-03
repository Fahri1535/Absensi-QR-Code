<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\QrCode;
use Illuminate\Auth\SessionGuard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;

class LoginController extends Controller
{
    /* ─── Show form ──────────────────────────────────────────── */

    public function showLoginForm()
    {
        if (auth()->check()) {
            return redirect($this->redirectTo());
        }

        return view('auth.login');
    }

    /* ─── Process login ──────────────────────────────────────── */

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $remember = $request->boolean('remember');

        if (! Auth::attempt($credentials, $remember)) {
            return back()
                ->withInput($request->only(['username', 'remember']))
                ->withErrors(['username' => 'Username atau password salah.']);
        }

        if (Auth::user()->role === 'karyawan') {
            $pending = $request->session()->pull('presensi_qr_token');
            if (
                $pending
                && QrCode::where('kode_qr', $pending)->where('is_active', true)->exists()
            ) {
                $request->session()->forget('url.intended');

                return redirect()->route('karyawan.presensi', ['t' => $pending]);
            }
        }

        return redirect()->intended($this->redirectTo());
    }

    /* ─── Logout ─────────────────────────────────────────────── */

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /* ─── Role-based redirect ────────────────────────────────── */

    protected function redirectTo(): string
    {
        return match(auth()->user()?->role) {
            'karyawan' => route('karyawan.dashboard'),
            'operator' => route('operator.dashboard'),
            'hrd'      => route('hrd.dashboard'),
            default    => '/',
        };
    }
}
