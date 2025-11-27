<?php

namespace App\Services\OrangTua;

use App\Models\Student;
use App\Repositories\Contracts\ParentPortalRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TeacherNoteService
{
    public function __construct(
        private readonly ParentPortalRepositoryInterface $repository
    ) {
    }

    public function list(Student $student, array $filters = []): LengthAwarePaginator
    {
        return $this->repository->noteFeed($student, $filters);
    }
}
