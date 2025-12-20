<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Guru\Concerns\BuildsAttendanceData;
use App\Http\Requests\Guru\StoreAttendanceRequest;
use App\Models\Attendance;
use App\Models\Schedule;
use App\Models\TeacherNote;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AttendanceController extends Controller
{
    use BuildsAttendanceData;
    public function index(Request $request)
    {
        $dataset = $this->buildAttendanceDataset($request);
        $records = $dataset['records'];

        return response()->json([
            'data' => $records->items(),
            'meta' => [
                'current_page' => $records->currentPage(),
                'last_page' => $records->lastPage(),
                'per_page' => $records->perPage(),
                'total' => $records->total(),
            ],
            'summary' => $dataset['summary'],
            'subjects' => $dataset['subjects'],
            'schedule_date' => $dataset['schedule_date'],
            'schedule_window' => $dataset['schedule_window'],
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
            ->when($request->filled('schedule_id'), fn ($builder) => $builder->where('schedule_id', $request->schedule_id))
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

        if ($this->scheduleInputLocked($schedule)) {
            $message = 'Presensi hanya tersedia ketika jadwal sudah dimulai.';

            if ($request->wantsJson()) {
                return response()->json(['message' => $message], 422);
            }

            return redirect()->back()->with('error', $message);
        }

        $payload['recorded_by'] = $request->user()->id;
        $payload['recorded_at'] = $payload['recorded_at'] ?? now();
        $payload['attendance_date'] = Carbon::parse(
            $payload['attendance_date']
                ?? $schedule->start_time?->toDateString()
                ?? now()->toDateString()
        )->toDateString();
        $payload['session_topic'] = $payload['session_topic']
            ?? $this->buildSubjectLabel($schedule->subject, $schedule->topic)
            ?? $schedule->topic;
        $payload['session_time'] = $payload['session_time']
            ?? (optional($schedule->start_time)?->format('H:i') && optional($schedule->end_time)?->format('H:i')
                ? optional($schedule->start_time)?->format('H:i') . ' - ' . optional($schedule->end_time)?->format('H:i')
                : null);
        $payload['input_channel'] = $payload['input_channel'] ?? 'web';
        $payload['status'] = $this->normalizeStatus($payload['status'] ?? $request->input('status'));

        $attendance = Attendance::updateOrCreate(
            [
                'student_id' => $payload['student_id'],
                'schedule_id' => $payload['schedule_id'],
                'attendance_date' => $payload['attendance_date'],
            ],
            $payload
        );

        // Force status to persist (handles legacy null rows and enum drift)
        if ($attendance->status !== $payload['status']) {
            $attendance->status = $payload['status'];
            $attendance->save();
        }

        $attendance->load(['student', 'schedule']);

        $this->syncAttendanceTeacherNote($attendance, $schedule, $request->user()->id);

        if ($request->wantsJson()) {
            return response()->json($attendance, 201);
        }

        return redirect()->back()->with('success', 'Data kehadiran berhasil disimpan.');
    }

    public function destroy(Request $request, Attendance $attendance)
    {
        $this->authorizeSchedule($attendance->schedule, $request->user()->id);

        if ($this->scheduleInputLocked($attendance->schedule)) {
            $message = 'Anda belum dapat mengubah presensi untuk jadwal yang belum dimulai.';

            if ($request->wantsJson()) {
                return response()->json(['message' => $message], 422);
            }

            return redirect()->back()->with('error', $message);
        }

        $attendance->delete();

        if ($request->wantsJson()) {
            return response()->noContent();
        }

        return redirect()->back()->with('success', 'Data kehadiran dihapus.');
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

    protected function scheduleInputLocked(?Schedule $schedule): bool
    {
        if (! $schedule) {
            return false;
        }

        // Only check the actual start time
        // Don't rely on status_badge which might be outdated
        if ($schedule->start_time?->isFuture()) {
            return true;
        }

        return false;
    }

}
