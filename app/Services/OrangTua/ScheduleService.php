<?php

namespace App\Services\OrangTua;

use App\Models\Student;
use App\Repositories\Contracts\ParentPortalRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ScheduleService
{
    public function __construct(
        private readonly ParentPortalRepositoryInterface $repository
    ) {
    }

    public function list(Student $student, array $filters = []): LengthAwarePaginator
    {
        return $this->repository->scheduleFeed($student, $filters);
    }

    public function calendar(Student $student, array $filters = []): Collection
    {
        return $this->repository->scheduleCalendar($student, $filters);
    }
}
