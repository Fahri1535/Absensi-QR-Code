<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\QrCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /* ─── Show form ──────────────────────────────────────────── */

    public function showLoginForm(Request $request)
    {
        if (auth()->check()) {
            return redirect($this->redirectTo());
        }

        // Pre-fill username from "Remember me" cookie
        $rememberedUsername = $request->cookie('remember_username') ?? '';

        return view('auth.login', compact('rememberedUsername'));
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

        // Set or clear "Remember username" cookie (1 year)
        if ($remember) {
            cookie()->queue('remember_username', $credentials['username'], 525600);
        } else {
            cookie()->queue(cookie()->forget('remember_username'));
        }

        if (in_array(Auth::user()->role, ['karyawan', 'hrd'])) {
            $pending = $request->session()->pull('presensi_qr_token');
            if (
                $pending
                && QrCode::where('kode_qr', $pending)->where('is_active', true)->exists()
            ) {
                $request->session()->forget('url.intended');
                $route = Auth::user()->role === 'hrd' ? 'hrd.presensi' : 'karyawan.presensi';
                return redirect()->route($route, ['t' => $pending]);
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
