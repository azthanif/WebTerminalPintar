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
    public function findStudentFor(User $user): ?Student
    {
        return $user->students()
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
            ->where('visibility', 'parent')
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
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['search'] ?? null, function ($query, $keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('topic', 'like', "%{$keyword}%")
                        ->orWhere('subject', 'like', "%{$keyword}%");
                });
            })
            ->orderByDesc('start_time')
            ->with(['teacher', 'materials'])
            ->paginate($filters['per_page'] ?? 10)
            ->withQueryString();
    }

    public function noteFeed(Student $student, array $filters = []): LengthAwarePaginator
    {
        return $student->teacherNotes()
            ->where('visibility', 'parent')
            ->when($filters['category'] ?? null, fn ($query, $category) => $query->where('category', $category))
            ->when($filters['search'] ?? null, function ($query, $keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('title', 'like', "%{$keyword}%")
                        ->orWhere('note', 'like', "%{$keyword}%");
                });
            })
            ->latest('recorded_at')
            ->with('teacher')
            ->paginate($filters['per_page'] ?? 10)
            ->withQueryString();
    }
}
