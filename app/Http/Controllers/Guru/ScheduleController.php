<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ScheduleController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('Guru/Jadwal', [
            'filters' => [
                'status' => $request->get('status'),
            ],
            'statusOptions' => ['Semua', 'Akan Datang', 'Berlangsung', 'Selesai', 'Dibatalkan'],
            'students' => Student::orderBy('name')->get(['id', 'name']),
        ]);
    }
}
