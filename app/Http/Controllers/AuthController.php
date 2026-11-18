<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();
            $user = Auth::user();

            $targetRoute = $user->isAdmin() ? route('dashboard') : route('my-visits.index');

            return redirect()->intended($targetRoute)
                ->with('success', 'Selamat datang kembali, ' . $user->name . '!');
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
        $user = User::findOrFail($id);
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
