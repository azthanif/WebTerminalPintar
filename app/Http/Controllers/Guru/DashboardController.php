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
            'title' => 'Dashboard Overview',
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
            ->where(function ($query) {
                $query->where('start_time', '>=', now())
                    ->orWhere(function ($q) {
                        $q->where('start_time', '<=', now())
                            ->where('end_time', '>=', now());
                    });
            })
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

        $attendanceStats = $this->normalizeSummary($attendanceStats);

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

    protected function normalizeSummary($rawSummary): array
    {
        $allowed = ['Hadir', 'Izin', 'Sakit', 'Alpha'];
        $normalized = array_fill_keys($allowed, 0);

        foreach ($rawSummary as $status => $total) {
            $clean = trim((string) $status);

            if ($clean === '' || ! in_array($clean, $allowed, true)) {
                continue;
            }

            $normalized[$clean] = ($normalized[$clean] ?? 0) + (int) $total;
        }

        return $normalized;
    }

    protected function normalizeStatus(?string $status): string
    {
        $clean = trim((string) $status);
        $allowed = ['Hadir', 'Izin', 'Sakit', 'Alpha'];

        return in_array($clean, $allowed, true) ? $clean : 'Hadir';
    }
}
