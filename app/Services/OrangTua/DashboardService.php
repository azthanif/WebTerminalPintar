<?php

namespace App\Services\OrangTua;

use App\Models\Student;
use App\Models\User;
use App\Repositories\Contracts\ParentPortalRepositoryInterface;
use App\Http\Resources\OrangTua\ScheduleResource; // Pastikan ini ada jika dipakai
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class DashboardService
{
    public function __construct(
        private readonly ParentPortalRepositoryInterface $repository
    ) {
    }

    // UBAH BAGIAN INI: Tambahkan parameter $studentId
    public function resolveStudent(User $user, ?int $studentId = null): ?Student
    {
        // Teruskan ID tersebut ke repository
        return $this->repository->findStudentFor($user, $studentId);
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

        $presentCount = $attendances->where('status', 'present')->count();
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