<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect($this->getRedirectUrl());
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $key = 'login:'.strtolower($request->input('email')).'|'.$request->ip();
        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($key, 5)) {
            $secs = \Illuminate\Support\Facades\RateLimiter::availableIn($key);
            return back()->withErrors(['email' => "Terlalu banyak percobaan. Coba lagi dalam {$secs} detik."]);
        }

        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, false)) {
            \Illuminate\Support\Facades\RateLimiter::clear($key);
            $request->session()->regenerate();

            $user = Auth::user();
            if (!$user->is_active) {
                Auth::logout();
                return back()->withErrors(['email' => 'Akun Anda telah dinonaktifkan.']);
            }

            return redirect($this->getRedirectUrl());
        }

        \Illuminate\Support\Facades\RateLimiter::hit($key, 60);
        return back()->withErrors(['email' => 'Email atau password salah.']);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }

    private function getRedirectUrl(): string
    {
        $user = Auth::user();
        return match ($user->role) {
            'superadmin' => '/superadmin/dashboard',
            'admin' => '/dashboard',
            'team_leader' => '/verification',
            'petugas_so' => '/entry',
            default => '/dashboard',
        };
    }
}
