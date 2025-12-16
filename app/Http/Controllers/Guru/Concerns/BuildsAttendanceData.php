<?php

namespace App\Http\Controllers\Guru\Concerns;

use App\Models\Attendance;
use App\Models\Schedule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

trait BuildsAttendanceData
{
    protected function buildAttendanceDataset(Request $request): array
    {
        $teacher = $request->user();
        $requestedSubject = $request->input('subject');
        $subjectFilter = $request->filled('subject') && $requestedSubject !== 'Semua'
            ? $requestedSubject
            : null;

        $scheduleDate = $this->resolveScheduleDate($request);

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

        return [
            'records' => $records,
            'summary' => $this->normalizeSummary($summary),
            'subjects' => $subjects,
            'schedule_date' => $scheduleDate,
            'schedule_window' => $scheduleWindow,
            'filters' => [
                'query' => $request->input('search', ''),
                'status' => $request->input('status', 'Semua'),
                'subject' => $subjectFilter ?? 'Semua',
                'page' => $records->currentPage(),
                'scheduleDate' => $scheduleDate,
                'scheduleId' => $request->input('schedule_id'),
            ],
        ];
    }

    protected function resolveScheduleDate(Request $request): string
    {
        try {
            if ($request->filled('schedule_date')) {
                return Carbon::parse($request->input('schedule_date'))->toDateString();
            }

            if ($request->filled('date')) {
                return Carbon::parse($request->input('date'))->toDateString();
            }
        } catch (\Throwable $e) {
            // Fallback handled below
        }

        return now()->toDateString();
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

    protected function normalizeSummary($rawSummary): array
    {
        $allowed = ['Hadir', 'Izin', 'Sakit', 'Alpha'];
        $normalized = array_fill_keys($allowed, 0);

        foreach ($rawSummary as $status => $total) {
            $clean = is_string($status) ? trim($status) : '';
            if ($clean === '') {
                continue;
            }

            $matched = collect($allowed)->first(fn ($option) => strcasecmp($option, $clean) === 0);
            if (! $matched) {
                continue;
            }

            $normalized[$matched] = ($normalized[$matched] ?? 0) + (int) $total;
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
