<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureParentHasStudent
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user && $user->hasRole('ortu') && $user->students()->doesntExist()) {
            abort(403, 'Akun ini belum terhubung dengan data siswa. Hubungi admin untuk melanjutkan.');
        }

        return $next($request);
    }
}
