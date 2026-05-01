<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
                ->withInput($request->only('username'))
                ->withErrors(['username' => 'Username atau password salah.']);
        }

        $request->session()->regenerate();

        /*
         * Jika TIDAK centang "Ingat saya":
         * set cookie_lifetime = 0 agar session mati saat browser ditutup.
         *
         * Jika centang "Ingat saya":
         * Laravel otomatis buat remember_token cookie yang tahan 5 tahun,
         * dan SESSION_LIFETIME di .env mengontrol berapa lama sesi aktif.
         */
        if (! $remember) {
            config(['session.expire_on_close' => true]);
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
