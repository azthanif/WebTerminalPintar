<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;

class PasswordChangeController extends Controller
{
    public function show()
    {
        return Inertia::render('Auth/ChangePassword');
    }

    public function update(Request $request)
    {
        $request->validate([
            'password' => ['required', 'confirmed', Password::defaults()],
        ], [
            'password.required' => 'Password baru wajib diisi.',
            'password.confirmed' => 'Konfirmasi password tidak cocok dengan password baru.',
            'password.min' => 'Password minimal harus :min karakter.',
        ]);

        $user = Auth::user();
        
        /** @var User $user */
        $user->forceFill([
            'password' => Hash::make($request->password),
            'must_change_password' => false,
        ])->save();

        if ($user->hasRole('admin')) {
            return redirect()->route('admin.dashboard')->with('success', 'Password berhasil diperbarui.');
        }

        if ($user->hasRole('ortu')) {
            return redirect()->route('orang-tua.dashboard')->with('success', 'Password berhasil diperbarui.');
        }

        if ($user->hasRole('guru')) {
            return redirect()->route('guru.dashboard')->with('success', 'Password berhasil diperbarui.');
        }

        return redirect()->intended('/')->with('success', 'Password berhasil diperbarui.');
    }
}
