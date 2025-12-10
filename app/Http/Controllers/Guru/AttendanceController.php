<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Http\Requests\Guru\StoreAttendanceRequest;
use App\Models\Attendance;
use App\Models\Schedule;
use App\Models\TeacherNote;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $teacher = $request->user();

        $records = Attendance::with(['student', 'schedule'])
            ->forTeacher($teacher)
            ->when($request->filled('search'), function ($query) use ($request) {
                $term = '%' . $request->search . '%';
                $query->where(function ($builder) use ($term) {
                    $builder->whereHas('student', fn ($q) => $q->where('name', 'like', $term))
                        ->orWhere('session_topic', 'like', $term)
                        ->orWhere('notes', 'like', $term)
                        ->orWhereHas('schedule', fn ($q) => $q->where('topic', 'like', $term));
                });
            })
            ->when($request->filled('status') && $request->status !== 'Semua', fn ($q) => $q->where('status', $request->status))
            ->latest('attendance_date')
            ->paginate($request->integer('per_page', 10));

        $summary = Attendance::forTeacher($teacher)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return response()->json([
            'data' => $records->items(),
            'meta' => [
                'current_page' => $records->currentPage(),
                'last_page' => $records->lastPage(),
                'per_page' => $records->perPage(),
                'total' => $records->total(),
            ],
            'summary' => $summary,
        ]);
    }

    public function store(StoreAttendanceRequest $request)
    {
        $payload = $request->validated();
        $schedule = Schedule::findOrFail($payload['schedule_id']);
        $this->authorizeSchedule($schedule, $request->user()->id);

        $payload['recorded_by'] = $request->user()->id;
        $payload['recorded_at'] = $payload['recorded_at'] ?? now();
        $payload['attendance_date'] = $payload['attendance_date'] ?? $schedule->start_time?->toDateString();

        $attendance = Attendance::updateOrCreate(
            [
                'student_id' => $payload['student_id'],
                'schedule_id' => $payload['schedule_id'],
                'attendance_date' => $payload['attendance_date'],
            ],
            $payload
        )->load(['student', 'schedule']);

        $this->syncAttendanceTeacherNote($attendance, $schedule, $request->user()->id);

        return response()->json($attendance, 201);
    }

    public function destroy(Request $request, Attendance $attendance)
    {
        $this->authorizeSchedule($attendance->schedule, $request->user()->id);
        $attendance->delete();

        return response()->noContent();
    }

    protected function authorizeSchedule(?Schedule $schedule, int $teacherId): void
    {
        abort_if(! $schedule, 404, 'Jadwal tidak ditemukan');
        abort_unless($schedule->teacher_id === $teacherId, 403, 'Anda tidak boleh mengubah kehadiran ini.');
    }

    protected function syncAttendanceTeacherNote(Attendance $attendance, Schedule $schedule, int $teacherId): void
    {
        $noteText = trim((string) ($attendance->notes ?? ''));

        if ($noteText === '') {
            TeacherNote::where('attendance_id', $attendance->id)->delete();
            return;
        }

        TeacherNote::updateOrCreate(
            ['attendance_id' => $attendance->id],
            [
                'student_id' => $attendance->student_id,
                'schedule_id' => $attendance->schedule_id,
                'attendance_id' => $attendance->id,
                'teacher_id' => $teacherId,
                'title' => $this->buildAttendanceNoteTitle($attendance, $schedule),
                'note' => $noteText,
                'category' => 'general',
                'visibility' => 'parent',
                'recorded_at' => $attendance->recorded_at ?? now(),
            ]
        );
    }

    protected function buildAttendanceNoteTitle(Attendance $attendance, Schedule $schedule): string
    {
        $dateLabel = $attendance->attendance_date
            ? $attendance->attendance_date->translatedFormat('d M Y')
            : now()->translatedFormat('d M Y');

        $sessionLabel = collect([
            $attendance->session_topic,
            $schedule->subject ?? null,
            $schedule->topic ?? null,
        ])
            ->filter()
            ->unique()
            ->implode(' - ');

        return trim('Catatan Kehadiran ' . $dateLabel . ($sessionLabel ? ' · ' . $sessionLabel : ''));
    }
}
