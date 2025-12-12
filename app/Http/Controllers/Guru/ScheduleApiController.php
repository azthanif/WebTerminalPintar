<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Http\Requests\Guru\StoreScheduleRequest;
use App\Http\Requests\Guru\UpdateScheduleRequest;
use App\Models\Attendance;
use App\Models\Schedule;
use Illuminate\Http\Request;

class ScheduleApiController extends Controller
{
    public function index(Request $request)
    {
        $teacher = $request->user();

        $showOnlyDeleted = $request->boolean('only_trashed');

        $query = Schedule::forTeacher($teacher)
            ->with(['students', 'materials']);

        if ($showOnlyDeleted) {
            $query->onlyTrashed();
        } elseif ($request->boolean('with_trashed')) {
            $query->withTrashed();
        }

        $query
            ->when($request->filled('status') && $request->status !== 'Semua', fn ($q) => $q->where('status_badge', $request->string('status')))
            ->when($request->filled('search'), function ($q) use ($request) {
                $q->where(function ($inner) use ($request) {
                    $inner->where('topic', 'like', "%{$request->search}%")
                        ->orWhere('subject', 'like', "%{$request->search}%");
                });
            })
            ->orderByDesc('start_time');

        $perPage = $request->integer('per_page', 10);
        $paginated = $query->paginate($perPage);

        $summary = Schedule::forTeacher($teacher)
            ->selectRaw('status_badge, COUNT(*) as total')
            ->groupBy('status_badge')
            ->pluck('total', 'status_badge');

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

        foreach ($ids as $studentId) {
            Attendance::updateOrCreate(
                [
                    'student_id' => $studentId,
                    'schedule_id' => $schedule->id,
                ],
                [
                    'attendance_date' => $attendanceDate,
                    'recorded_by' => $teacherId,
                    'recorded_at' => $schedule->start_time ?? now(),
                    'status' => null,
                    'session_topic' => $this->buildSessionTopicLabel($schedule),
                    'session_time' => $this->buildSessionTimeLabel($schedule),
                ]
            );
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
