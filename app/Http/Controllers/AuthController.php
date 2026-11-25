<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Tampilkan formulir login.
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            return Auth::user()->isAdmin()
                ? redirect()->route('dashboard')
                : redirect()->route('my-visits.index');
        }

        $demoUsers = User::with('sales')->orderBy('role', 'asc')->orderBy('id', 'asc')->get();

        return view('auth.login', compact('demoUsers'));
    }

    /**
     * Proses autentikasi login.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        $remember = $request->has('remember');

        // Batasi percobaan login berulang (per email + IP)
        $throttleKey = Str::lower($credentials['email']) . '|' . $request->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return back()->withErrors([
                'email' => "Terlalu banyak percobaan login. Coba lagi dalam {$seconds} detik.",
            ])->onlyInput('email');
        }

        // Hanya akun aktif yang boleh masuk
        if (Auth::attempt($credentials + ['is_active' => true], $remember)) {
            RateLimiter::clear($throttleKey);
            $request->session()->regenerate();
            $user = Auth::user();

            $targetRoute = $user->isAdmin() ? route('dashboard') : route('my-visits.index');

            return redirect()->intended($targetRoute)
                ->with('success', 'Selamat datang kembali, ' . $user->name . '!');
        }

        RateLimiter::hit($throttleKey, 60);

        // Kata sandi benar tetapi akun dinonaktifkan: beri pesan yang jelas
        $existing = User::where('email', $credentials['email'])->first();
        if ($existing && !$existing->is_active && Hash::check($credentials['password'], $existing->password)) {
            return back()->withErrors([
                'email' => 'Akun Anda dinonaktifkan. Hubungi admin.',
            ])->onlyInput('email');
        }

        return back()->withErrors([
            'email' => 'Kombinasi email dan kata sandi tidak cocok.',
        ])->onlyInput('email');
    }

    /**
     * Fitur Login Cepat (One-Click Demo Login untuk Evaluasi & Pengujian).
     */
    public function quickLogin($id)
    {
        // Login tanpa password hanya untuk pengembangan lokal.
        abort_unless(app()->environment('local'), 404);

        $user = User::findOrFail($id);
        abort_unless($user->is_active, 403, 'Akun nonaktif.');
        Auth::login($user);
        request()->session()->regenerate();

        $targetRoute = $user->isAdmin() ? route('dashboard') : route('my-visits.index');

        return redirect($targetRoute)
            ->with('success', "Beralih akun ke {$user->name} ({$user->role}) berhasil.");
    }

    /**
     * Logout pengguna dari aplikasi.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Anda telah berhasil keluar dari sistem.');
    }
}
