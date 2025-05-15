<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Providers\RouteServiceProvider; // Untuk redirect setelah login

class CustomLoginController extends Controller
{
    /**
     * Menampilkan form login.
     */
    public function showLoginForm()
    {
        // Anda perlu membuat view ini
        return view('auth.login'); // Contoh: resources/views/auth/login.blade.php
    }

    /**
     * Menangani permintaan login.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            // Ganti 'email' dengan 'username' jika login dengan username
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        // Parameter kedua 'remember' adalah opsional
        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            // Redirect ke halaman yang dituju sebelumnya atau ke HOME (default /dashboard)
            // Pastikan RouteServiceProvider::HOME sudah didefinisikan dengan benar
            return redirect()->intended(RouteServiceProvider::HOME);
        }

        // Jika autentikasi gagal
        throw ValidationException::withMessages([
            // Ganti 'email' dengan field login Anda jika berbeda
            'email' => [trans('auth.failed')], // Menggunakan terjemahan Laravel standar
        ]);
    }

    /**
     * Menangani permintaan logout.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/'); // Redirect ke halaman utama atau halaman login
    }
}
