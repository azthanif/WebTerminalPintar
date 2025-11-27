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
        ]);

        $loginValue = $credentials['email'];

        $user = User::query()
            ->where('email', $loginValue)
            ->orWhere('name', $loginValue)
            ->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return back()->withErrors([
                'email' => 'Kredensial yang diberikan tidak cocok dengan data kami.',
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
