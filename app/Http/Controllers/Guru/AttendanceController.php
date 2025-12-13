<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Http\Requests\Guru\StoreAttendanceRequest;
use App\Models\Attendance;
use App\Models\Schedule;
use App\Models\TeacherNote;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $teacher = $request->user();
        $subjectFilter = $request->filled('subject') && $request->subject !== 'Semua'
            ? $request->subject
            : null;
        try {
            $scheduleDate = $request->filled('schedule_date')
                ? Carbon::parse($request->input('schedule_date'))->toDateString()
                : now()->toDateString();
        } catch (\Throwable $e) {
            $scheduleDate = now()->toDateString();
        }

        $baseQuery = Attendance::forTeacher($teacher);
        $filteredQuery = $this->applyAttendanceFilters($baseQuery, $request, $subjectFilter, $scheduleDate);

        $records = (clone $filteredQuery)
            ->with(['student', 'schedule'])
            ->latest('attendance_date')
            ->paginate($request->integer('per_page', 10));

        $summary = (clone $filteredQuery)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $subjectsFromSchedule = Schedule::forTeacher($teacher)
            ->whereDate('start_time', $scheduleDate)
            ->get(['subject', 'topic'])
            ->map(fn ($row) => $this->buildSubjectLabel($row->subject, $row->topic))
            ->filter();

        $subjectsFromAttendance = Attendance::forTeacher($teacher)
            ->whereDate('attendance_date', $scheduleDate)
            ->whereHas('schedule', fn ($query) => $query->whereNull('deleted_at'))
            ->with('schedule')
            ->get(['session_topic', 'schedule_id'])
            ->map(function ($row) {
                return $this->buildSubjectLabel(
                    optional($row->schedule)->subject,
                    optional($row->schedule)->topic
                ) ?? $row->session_topic;
            })
            ->filter();

        $subjects = $subjectsFromSchedule
            ->merge($subjectsFromAttendance)
            ->filter()
            ->unique()
            ->sort()
            ->values();

        $scheduleWindow = $subjectFilter
            ? $this->buildScheduleWindow($teacher->id, $subjectFilter, $scheduleDate)
            : null;

        return response()->json([
            'data' => $records->items(),
            'meta' => [
                'current_page' => $records->currentPage(),
                'last_page' => $records->lastPage(),
                'per_page' => $records->perPage(),
                'total' => $records->total(),
            ],
            'summary' => $summary,
            'subjects' => $subjects,
            'schedule_date' => $scheduleDate,
            'schedule_window' => $scheduleWindow,
        ]);
    }

    protected function applyAttendanceFilters(Builder $query, Request $request, ?string $subjectFilter, ?string $scheduleDate): Builder
    {
        return $query
            ->when($request->filled('search'), function ($builder) use ($request) {
                $term = '%' . $request->search . '%';
                $builder->where(function ($inner) use ($term) {
                    $inner->whereHas('student', fn ($q) => $q->where('name', 'like', $term))
                        ->orWhere('session_topic', 'like', $term)
                        ->orWhere('notes', 'like', $term)
                        ->orWhereHas('schedule', fn ($q) => $q->where('topic', 'like', $term));
                });
            })
            ->when($request->filled('status') && $request->status !== 'Semua', fn ($builder) => $builder->where('status', $request->status))
            ->when($scheduleDate, fn ($builder) => $builder->whereDate('attendance_date', $scheduleDate))
            ->when($subjectFilter, function ($builder) use ($subjectFilter) {
                $labelExpression = $this->subjectLabelExpression();
                $builder->where(function ($inner) use ($subjectFilter, $labelExpression) {
                    $inner->whereHas('schedule', function ($scheduleQuery) use ($subjectFilter, $labelExpression) {
                        $scheduleQuery->withTrashed()->where(function ($query) use ($subjectFilter, $labelExpression) {
                            $query->whereRaw("{$labelExpression} = ?", [$subjectFilter])
                                ->orWhere('subject', $subjectFilter)
                                ->orWhere('topic', $subjectFilter);
                        });
                    })
                        ->orWhere('session_topic', $subjectFilter);
                });
            });
    }

    public function store(StoreAttendanceRequest $request)
    {
        $payload = $request->validated();
        $schedule = Schedule::findOrFail($payload['schedule_id']);
        $this->authorizeSchedule($schedule, $request->user()->id);

        $payload['recorded_by'] = $request->user()->id;
        $payload['recorded_at'] = $payload['recorded_at'] ?? now();
        $payload['attendance_date'] = $payload['attendance_date'] ?? $schedule->start_time?->toDateString();
        $payload['session_topic'] = $payload['session_topic']
            ?? $this->buildSubjectLabel($schedule->subject, $schedule->topic)
            ?? $schedule->topic;

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

    protected function subjectLabelExpression(): string
    {
        return "TRIM(BOTH '-' FROM CONCAT_WS('-', subject, topic))";
    }

    protected function buildSubjectLabel(?string $subject, ?string $topic): ?string
    {
        $parts = collect([$subject, $topic])
            ->map(fn ($value) => is_string($value) ? trim($value) : $value)
            ->filter(fn ($value) => filled($value));

        return $parts->isEmpty() ? null : $parts->values()->implode('-');
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

    protected function buildScheduleWindow(int $teacherId, string $subjectLabel, string $scheduleDate): ?array
    {
        $matchingSchedule = Schedule::query()
            ->where('teacher_id', $teacherId)
            ->whereNull('deleted_at')
            ->whereDate('start_time', $scheduleDate)
            ->get()
            ->first(fn ($schedule) => $this->buildSubjectLabel($schedule->subject, $schedule->topic) === $subjectLabel);

        if (! $matchingSchedule || ! $matchingSchedule->start_time) {
            return null;
        }

        $secondsUntil = now()->diffInSeconds($matchingSchedule->start_time, false);

        return [
            'starts_at' => $matchingSchedule->start_time->toIso8601String(),
            'seconds_until' => $secondsUntil > 0 ? $secondsUntil : 0,
            'schedule_label' => $this->buildSubjectLabel($matchingSchedule->subject, $matchingSchedule->topic),
        ];
    }
}
