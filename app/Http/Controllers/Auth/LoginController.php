<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class LoginController extends Controller
{
    /**
     * Tampilkan form login (Halaman Inertia)
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login');
    }

    /**
     * Proses submit login
     */
    public function store(Request $request)
    {
        // validasi input dasar (gunakan field "email" sebagai username/email)
        $credentials = $request->validate([
            'email'    => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'email.required'    => 'Email atau nama pengguna wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $loginValue = $credentials['email'];

        $user = User::query()
            ->where('email', $loginValue)
            ->orWhere('name', $loginValue)
            ->first();

        if (! $user) {
            return back()->withErrors([
                'email' => 'Email atau nama pengguna tidak ditemukan.',
            ])->onlyInput('email');
        }

        if (! Hash::check($credentials['password'], $user->password)) {
            return back()->withErrors([
                'password' => 'Password yang Anda masukkan salah.',
            ])->onlyInput('email');
        }

        if ($user->is_active === false) {
            return back()->withErrors([
                'email' => 'Akun Anda sedang dinonaktifkan. Hubungi administrator.',
            ]);
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        if ($user->hasRole('admin')) {
            return redirect()->intended(route('admin.dashboard'));
        }

        if ($user->hasRole('ortu')) {
            return redirect()->intended(route('orang-tua.dashboard'));
        }

        if ($user->hasRole('guru')) {
            return redirect()->intended(route('guru.dashboard'));
        }

        return redirect()->intended('/');
    }

    /**
     * Logout user
     */
    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
