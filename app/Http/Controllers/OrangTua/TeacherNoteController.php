<?php

namespace App\Http\Controllers\OrangTua;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrangTua\TeacherNoteFilterRequest;
use App\Http\Resources\OrangTua\StudentResource;
use App\Http\Resources\OrangTua\TeacherNoteResource;
use App\Services\OrangTua\DashboardService;
use App\Services\OrangTua\TeacherNoteService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Inertia\Response;

class TeacherNoteController extends Controller
{
    public function __construct(
        private readonly DashboardService $dashboardService,
        private readonly TeacherNoteService $teacherNoteService
    ) {
    }

    public function index(TeacherNoteFilterRequest $request): Response
    {
        $student = $this->dashboardService->resolveStudent($request->user());

        abort_if(is_null($student), 404, 'Data siswa tidak ditemukan untuk akun ini.');

        $filters = $request->validated();
        $notes = $this->teacherNoteService->list($student, $filters);

        return Inertia::render('OrangTua/TeacherNotes', [
            'student' => StudentResource::make($student),
            'filters' => $filters,
            'notes' => TeacherNoteResource::collection($notes),
            'categoryOptions' => ['behavior', 'academic', 'communication', 'general'],
        ]);
    }

    public function data(TeacherNoteFilterRequest $request): AnonymousResourceCollection
    {
        $student = $this->dashboardService->resolveStudent($request->user());

        abort_if(is_null($student), 404);

        return TeacherNoteResource::collection(
            $this->teacherNoteService->list($student, $request->validated())
        );
    }
}
