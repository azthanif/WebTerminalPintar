<?php

namespace App\Http\Controllers\OrangTua;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StudentSwitcherController extends Controller
{
    /**
     * Handle permintaan untuk mengganti siswa aktif.
     */
    public function __invoke(Request $request): RedirectResponse
    {
        // 1. Validasi input
        $validated = $request->validate([
            'student_id' => ['required', 'integer', 'exists:students,id'],
        ]);

        $studentId = $validated['student_id'];
        $user = $request->user();

        // 2. KEAMANAN: Pastikan siswa ini benar-benar anak dari user yang login
        // Kita cek apakah ID anak ini ada di dalam daftar anak milik user
        $isMyChild = $user->students()->where('id', $studentId)->exists();

        if (! $isMyChild) {
            abort(403, 'Anda tidak memiliki akses ke data siswa ini.');
        }

        // 3. Simpan ID ke Session
        // Key ini akan dibaca oleh ParentPortalRepository nanti
        session(['parent_portal_student_id' => $studentId]);

        // 4. Kembali ke halaman sebelumnya dengan pesan sukses
        return back()->with('success', 'Berhasil beralih profil siswa.');
    }
}