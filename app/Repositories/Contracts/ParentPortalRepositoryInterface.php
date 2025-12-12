<?php

namespace App\Repositories\Contracts;

use App\Models\Student;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface ParentPortalRepositoryInterface
{
    // Tambahkan parameter ?int $studentId = null
    public function findStudentFor(User $user, ?int $studentId = null): ?Student;

    public function latestAttendances(Student $student, int $limit = 5): Collection;

    public function latestNotes(Student $student, int $limit = 5): Collection;

    public function upcomingSchedules(Student $student, int $limit = 3): Collection;

    public function scheduleFeed(Student $student, array $filters = []): LengthAwarePaginator;

    public function noteFeed(Student $student, array $filters = []): LengthAwarePaginator;
}