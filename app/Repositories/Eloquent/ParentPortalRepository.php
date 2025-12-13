<?php

namespace App\Repositories\Eloquent;

use App\Models\Schedule;
use App\Models\Student;
use App\Models\TeacherNote;
use App\Models\User;
use App\Repositories\Contracts\ParentPortalRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ParentPortalRepository implements ParentPortalRepositoryInterface
{
    public function findStudentFor(User $user, ?int $studentId = null): ?Student
    {
        return $user->students()
            ->when($studentId, fn ($query) => $query->where('id', $studentId))
            ->with(['attendances' => fn ($query) => $query->latest('attendance_date')->limit(10)])
            ->with(['teacherNotes' => fn ($query) => $query->latest('recorded_at')->limit(10)])
            ->first();
    }

    public function latestAttendances(Student $student, int $limit = 5): Collection
    {
        return $student->attendances()
            ->latest('attendance_date')
            ->limit($limit)
            ->get();
    }

    public function latestNotes(Student $student, int $limit = 5): Collection
    {
        return $student->teacherNotes()
            ->where(function ($query) {
                $query->where('visibility', 'parent')
                    ->orWhereNull('visibility');
            })
            ->latest('recorded_at')
            ->limit($limit)
            ->with('teacher')
            ->get();
    }

    public function upcomingSchedules(Student $student, int $limit = 3): Collection
    {
        return $student->schedules()
            ->where('start_time', '>=', Carbon::now())
            ->orderBy('start_time')
            ->with(['teacher', 'materials'])
            ->limit($limit)
            ->get();
    }

    public function scheduleFeed(Student $student, array $filters = []): LengthAwarePaginator
    {
        return $student->schedules()
            // --- PERBAIKAN LOGIKA STATUS ---
            ->when($filters['status'] ?? null, function ($query, $status) {
                // Map status Bahasa Inggris (dari Request) ke Bahasa Indonesia (di Database)
                $dbStatus = match ($status) {
                    'scheduled' => 'Akan Datang',
                    'completed' => 'Selesai',
                    'canceled'  => 'Dibatalkan',
                    'ongoing'   => 'Berlangsung', // Opsional, jaga-jaga jika nanti ada filter ongoing
                    default     => $status,       // Fallback jika tidak ada di map
                };

                $query->where('status', $dbStatus);
            })
            // --------------------------------
            ->when($filters['search'] ?? null, function ($query, $keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('topic', 'like', "%{$keyword}%")
                        ->orWhere('subject', 'like', "%{$keyword}%");
                });
            })
            ->when($filters['start_date'] ?? null, fn ($q, $date) => $q->whereDate('start_time', '>=', $date))
            ->when($filters['end_date'] ?? null, fn ($q, $date) => $q->whereDate('start_time', '<=', $date))
            ->orderByDesc('start_time')
            ->with(['teacher', 'materials'])
            ->paginate($filters['per_page'] ?? 10)
            ->withQueryString();
    }

    public function scheduleCalendar(Student $student, array $filters = []): Collection
    {
        $start = Carbon::parse($filters['start_date'] ?? now()->startOfWeek())->startOfDay();
        $end = Carbon::parse($filters['end_date'] ?? now()->endOfWeek())->endOfDay();

        return $student->schedules()
            ->whereBetween('start_time', [$start, $end])
            ->when($filters['status'] ?? null, function ($query, $status) {
                $dbStatus = match ($status) {
                    'scheduled' => 'Akan Datang',
                    'completed' => 'Selesai',
                    'canceled'  => 'Dibatalkan',
                    'ongoing'   => 'Berlangsung',
                    default     => $status,
                };

                $query->where('status', $dbStatus);
            })
            ->when($filters['search'] ?? null, function ($query, $keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('topic', 'like', "%{$keyword}%")
                        ->orWhere('subject', 'like', "%{$keyword}%");
                });
            })
            ->orderBy('start_time')
            ->with(['teacher', 'materials'])
            ->get();
    }

    public function noteFeed(Student $student, array $filters = []): LengthAwarePaginator
    {
        return $student->teacherNotes()
            ->where(function ($query) {
                $query->where('visibility', 'parent')
                    ->orWhereNull('visibility');
            })
            ->when($filters['category'] ?? null, fn ($query, $category) => $query->where('category', $category))
            ->when($filters['search'] ?? null, function ($query, $keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('title', 'like', "%{$keyword}%")
                        ->orWhere('note', 'like', "%{$keyword}%");
                });
            })
            ->when($filters['start_date'] ?? null, fn ($q, $date) => $q->whereDate('recorded_at', '>=', $date))
            ->when($filters['end_date'] ?? null, fn ($q, $date) => $q->whereDate('recorded_at', '<=', $date))
            ->latest('recorded_at')
            ->with('teacher')
            ->paginate($filters['per_page'] ?? 10)
            ->withQueryString();
    }
}