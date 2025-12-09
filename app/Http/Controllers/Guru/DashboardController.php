<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Schedule;
use App\Models\TeacherNote;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('Guru/Dashboard', [
            'summary' => $this->buildSummary($request->user()),
        ]);
    }

    public function summary(Request $request)
    {
        return response()->json($this->buildSummary($request->user()));
    }

    protected function buildSummary($teacher): array
    {
        $scheduleQuery = Schedule::forTeacher($teacher)
            ->with(['students', 'materials'])
            ->latest('start_time');

        $upcoming = (clone $scheduleQuery)
            ->whereNull('deleted_at')
            ->where('start_time', '>=', now())
            ->orderBy('start_time')
            ->limit(5)
            ->get()
            ->map(fn ($schedule) => [
                'id' => $schedule->id,
                'topic' => $schedule->topic,
                'start_time' => $schedule->start_time,
                'status_badge' => $schedule->status_badge,
                'status_color' => $schedule->status_color,
                'students' => $schedule->students->pluck('name')->all(),
            ]);

        $attendanceStats = Attendance::forTeacher($teacher)
            ->selectRaw("status, COUNT(*) as total")
            ->groupBy('status')
            ->pluck('total', 'status')
            ->all();

        $notes = TeacherNote::where('teacher_id', $teacher->id)
            ->latest('recorded_at')
            ->limit(5)
            ->get()
            ->map(fn ($note) => [
                'id' => $note->id,
                'title' => $note->title,
                'student' => $note->student?->name,
                'category' => $note->category,
                'tag_color' => $note->tag_color,
                'recorded_at' => $note->recorded_at,
            ]);

        return [
            'upcomingSchedules' => $upcoming,
            'attendanceStats' => $attendanceStats,
            'recentNotes' => $notes,
        ];
    }
}
