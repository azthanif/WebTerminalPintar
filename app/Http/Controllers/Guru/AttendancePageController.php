<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class AttendancePageController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $initialSubject = $request->query('subject');
        $initialScheduleDate = $request->filled('date')
            ? Carbon::parse($request->query('date'))->toDateString()
            : now()->toDateString();

        return Inertia::render('Guru/Kehadiran', [
            'title' => 'Kehadiran',
            'initialSubject' => $initialSubject,
            'initialScheduleDate' => $initialScheduleDate,
        ]);
    }
}
