<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Http\Requests\Guru\StoreScheduleRequest;
use App\Http\Requests\Guru\UpdateScheduleRequest;
use App\Models\Attendance;
use App\Models\Schedule;
use DateTimeZone;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ScheduleApiController extends Controller
{
    public function index(Request $request)
    {
        $teacher = $request->user();
        $timezone = $this->resolveTimezone($request->input('timezone'));
        $now = Carbon::now($timezone);

        $showOnlyDeleted = $request->boolean('only_trashed');

        $query = Schedule::forTeacher($teacher)
            ->with(['students', 'materials']);

        if ($showOnlyDeleted) {
            $query->onlyTrashed();
        } elseif ($request->boolean('with_trashed')) {
            $query->withTrashed();
        }

        $status = $request->input('status');

        if ($status && $status !== 'Semua') {
            $this->applyStatusFilter($query, $status, $now);
        }

        $query
            ->when($request->filled('search'), function ($q) use ($request) {
                $q->where(function ($inner) use ($request) {
                    $inner->where('topic', 'like', "%{$request->search}%")
                        ->orWhere('subject', 'like', "%{$request->search}%");
                });
            })
            ->orderByDesc('start_time');

        $perPage = $request->integer('per_page', 10);
        $paginated = $query->paginate($perPage);

        $statusExpression = $this->statusCaseExpression();
        $statusBindings = $this->statusCaseBindings($now);

        $summary = Schedule::forTeacher($teacher)
            ->selectRaw("{$statusExpression} as computed_status, COUNT(*) as total", $statusBindings)
            ->groupBy('computed_status')
            ->pluck('total', 'computed_status');

        return response()->json([
            'data' => $paginated->items(),
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'total' => $paginated->total(),
            ],
            'summary' => $summary,
        ]);
    }

    public function store(StoreScheduleRequest $request)
    {
        $payload = $request->validated();
        $timezone = $this->resolveTimezone($payload['timezone'] ?? null);
        $payload = $this->normalizeSchedulePayloadTimes($payload, $timezone);
        unset($payload['timezone']);
        $studentIds = collect($payload['student_ids'] ?? [])
            ->filter()
            ->unique()
            ->values()
            ->all();

        unset($payload['student_ids']);

        $payload['teacher_id'] = $request->user()->id;
        $payload['status_badge'] = $payload['status_badge'] ?? 'Akan Datang';
        $payload['student_id'] = $studentIds[0] ?? null;

        $schedule = Schedule::create($payload);
        $schedule->students()->sync($studentIds);
        $schedule->refreshStatusBadge();
        $this->syncAttendanceSessions($schedule, $studentIds, $request->user()->id);

        return response()->json($schedule->load(['students', 'materials']), 201);
    }

    public function update(UpdateScheduleRequest $request, Schedule $schedule)
    {
        $this->authorizeSchedule($schedule, $request->user()->id);

        $payload = $request->validated();
        $timezone = $this->resolveTimezone($payload['timezone'] ?? null);
        $payload = $this->normalizeSchedulePayloadTimes($payload, $timezone);
        unset($payload['timezone']);
        $studentIds = null;

        if (array_key_exists('student_ids', $payload)) {
            $studentIds = collect($payload['student_ids'] ?? [])
                ->filter()
                ->unique()
                ->values()
                ->all();

            unset($payload['student_ids']);
            $payload['student_id'] = $studentIds[0] ?? null;

            $schedule->students()->sync($studentIds);
        }

        $schedule->update($payload);
        $schedule->refreshStatusBadge();
        $schedule->refresh();
        $this->syncAttendanceSessions($schedule, $studentIds, $request->user()->id);

        return response()->json($schedule->fresh(['students', 'materials']));
    }

    public function destroy(Request $request, Schedule $schedule)
    {
        $this->authorizeSchedule($schedule, $request->user()->id);
        $schedule->delete();

        return response()->noContent();
    }

    public function restore(Request $request, $schedule)
    {
        $model = Schedule::withTrashed()->findOrFail($schedule);
        $this->authorizeSchedule($model, $request->user()->id);
        $model->restore();
        $model->refreshStatusBadge();

        return response()->json($model->fresh(['students', 'materials']));
    }

    protected function applyStatusFilter(Builder $query, string $status, Carbon $now): void
    {
        $expression = $this->statusCaseExpression();
        $bindings = array_merge($this->statusCaseBindings($now), [$status]);

        $query->whereRaw("{$expression} = ?", $bindings);
    }

    protected function statusCaseExpression(): string
    {
        return <<<'SQL'
(CASE
    WHEN status_badge = 'Dibatalkan' THEN 'Dibatalkan'
    WHEN start_time IS NULL THEN COALESCE(NULLIF(status_badge, ''), 'Akan Datang')
    WHEN start_time > ? THEN 'Akan Datang'
    WHEN start_time <= ? AND (
        (end_time IS NOT NULL AND end_time >= ?)
        OR (end_time IS NULL AND start_time = ?)
    ) THEN 'Berlangsung'
    ELSE 'Selesai'
END)
SQL;
    }

    protected function statusCaseBindings(Carbon $now): array
    {
        return [
            $now->copy(),
            $now->copy(),
            $now->copy(),
            $now->copy(),
        ];
    }

    protected function resolveTimezone(?string $timezone): string
    {
        if (! $timezone) {
            return config('app.timezone', 'UTC');
        }

        try {
            return (new DateTimeZone($timezone))->getName();
        } catch (\Throwable $e) {
            return config('app.timezone', 'UTC');
        }
    }

    protected function normalizeSchedulePayloadTimes(array $payload, string $timezone): array
    {
        $appTimezone = config('app.timezone', 'UTC');

        foreach (['start_time', 'end_time'] as $field) {
            if (! empty($payload[$field])) {
                $payload[$field] = Carbon::parse($payload[$field], $timezone)
                    ->setTimezone($appTimezone);
            }
        }

        return $payload;
    }

    protected function syncAttendanceSessions(Schedule $schedule, ?array $studentIds, int $teacherId): void
    {
        $ids = $studentIds ?? $schedule->students()->pluck('students.id')->all();

        if (empty($ids)) {
            Attendance::where('schedule_id', $schedule->id)->delete();
            return;
        }

        $existing = Attendance::where('schedule_id', $schedule->id)
            ->get()
            ->keyBy('student_id');

        foreach ($existing as $studentId => $attendance) {
            if (! in_array($studentId, $ids, true)) {
                $attendance->delete();
            }
        }

        $attendanceDate = optional($schedule->start_time)?->toDateString() ?? now()->toDateString();

        $sessionTopic = $this->buildSessionTopicLabel($schedule);
        $sessionTime = $this->buildSessionTimeLabel($schedule);

        foreach ($ids as $studentId) {
            $attendance = Attendance::firstOrNew([
                'student_id' => $studentId,
                'schedule_id' => $schedule->id,
            ]);

            if (! $attendance->exists) {
                $attendance->attendance_date = $attendanceDate;
                $attendance->recorded_by = $teacherId;
                $attendance->recorded_at = $schedule->start_time ?? now();
                $attendance->status = null;
            } else {
                if (! $attendance->attendance_date) {
                    $attendance->attendance_date = $attendanceDate;
                }
                if (! $attendance->recorded_at) {
                    $attendance->recorded_at = $schedule->start_time ?? now();
                }
            }

            $attendance->session_topic = $sessionTopic;
            $attendance->session_time = $sessionTime;
            $attendance->save();
        }
    }

    protected function buildSessionTopicLabel(Schedule $schedule): ?string
    {
        $parts = collect([$schedule->subject, $schedule->topic])
            ->filter(fn ($value) => filled($value));

        return $parts->isEmpty() ? null : $parts->unique()->implode('-');
    }

    protected function buildSessionTimeLabel(Schedule $schedule): ?string
    {
        if (! $schedule->start_time) {
            return null;
        }

        $start = $schedule->start_time->format('H:i');

        if (! $schedule->end_time) {
            return $start;
        }

        return sprintf('%s - %s', $start, $schedule->end_time->format('H:i'));
    }

    protected function authorizeSchedule(Schedule $schedule, int $teacherId): void
    {
        abort_unless($schedule->teacher_id === $teacherId, 403, 'Anda tidak boleh mengubah jadwal ini.');
    }
}
