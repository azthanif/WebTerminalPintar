<?php

namespace App\Services\OrangTua;

use App\Models\Student;
use App\Models\User;
use App\Repositories\Contracts\ParentPortalRepositoryInterface;
use App\Http\Resources\OrangTua\ScheduleResource; // Pastikan ini ada jika dipakai
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Session;

class DashboardService
{
    public function __construct(
        private readonly ParentPortalRepositoryInterface $repository
    ) {
    }

    public function resolveStudent(User $user, ?int $studentId = null): ?Student
    {
        $student = $this->repository->findStudentFor($user, $studentId);

        if (! $student && $studentId) {
            $student = $this->repository->findStudentFor($user);
        }

        if ($student && $studentId !== $student->id) {
            Session::put('parent_portal_student_id', $student->id);
        }

        return $student;
    }

    public function buildSummary(Student $student): array
    {
        $attendances = $this->repository->latestAttendances($student, 30);
        $notesCount = $student->teacherNotes()
            ->whereMonth('recorded_at', Carbon::now()->month)
            ->whereYear('recorded_at', Carbon::now()->year)
            ->count();

        $sessionsThisMonth = $student->schedules()
            ->whereMonth('start_time', Carbon::now()->month)
            ->whereYear('start_time', Carbon::now()->year)
            ->count();

        $presentCount = $attendances->where('status', 'Hadir')->count();
        $attendanceRate = $attendances->count() ? round(($presentCount / $attendances->count()) * 100, 1) : 0;

        $nextSchedule = $this->repository->upcomingSchedules($student, 1)->first();

        return [
            'attendance_rate' => $attendanceRate,
            'sessions_this_month' => $sessionsThisMonth,
            'notes_this_month' => $notesCount,
            'next_schedule' => $nextSchedule ? ScheduleResource::make($nextSchedule)->resolve() : null,
        ];
    }

    public function latestNotes(Student $student, int $limit = 5): Collection
    {
        return $this->repository->latestNotes($student, $limit);
    }

    public function upcomingSchedules(Student $student, int $limit = 3): Collection
    {
        return $this->repository->upcomingSchedules($student, $limit);
    }

    public function recentAttendances(Student $student, int $limit = 5): Collection
    {
        return $this->repository->latestAttendances($student, $limit);
    }
}