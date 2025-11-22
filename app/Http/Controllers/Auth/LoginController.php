<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        // validasi input
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        // cek kredensial
        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors([
                'email' => 'Email atau password salah.',
            ])->onlyInput('email');
        }

        // regenerate session
        $request->session()->regenerate();

        // redirect ke halaman yang diminta sebelumnya,
        // kalau tidak ada, arahkan ke halaman admin
        return redirect()->intended(route('admin.users.index'));
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
