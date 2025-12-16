<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Guru\Concerns\BuildsAttendanceData;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AttendancePageController extends Controller
{
    use BuildsAttendanceData;

    public function __invoke(Request $request): Response
    {
        $dataset = $this->buildAttendanceDataset($request);
        $records = $dataset['records'];
        $initialSubject = $request->query('subject');
        $initialScheduleId = $request->query('schedule_id');

        return Inertia::render('Guru/Kehadiran', [
            'title' => 'Rekap Kehadiran Siswa',
            'initialSubject' => $initialSubject,
            'initialScheduleDate' => $dataset['schedule_date'],
            'initialScheduleId' => $initialScheduleId,
            'attendance' => [
                'data' => $records->items(),
                'meta' => [
                    'current_page' => $records->currentPage(),
                    'last_page' => $records->lastPage(),
                    'per_page' => $records->perPage(),
                    'total' => $records->total(),
                ],
            ],
            'summary' => $dataset['summary'],
            'subjects' => $dataset['subjects'],
            'scheduleWindow' => $dataset['schedule_window'],
            'filters' => $dataset['filters'],
        ]);
    }
}
