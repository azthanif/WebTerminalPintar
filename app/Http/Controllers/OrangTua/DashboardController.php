<?php

namespace App\Http\Controllers\OrangTua;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrangTua\AttendanceResource;
use App\Http\Resources\OrangTua\ScheduleResource;
use App\Http\Resources\OrangTua\StudentResource;
use App\Http\Resources\OrangTua\TeacherNoteResource;
use App\Services\OrangTua\DashboardService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(private readonly DashboardService $dashboardService)
    {
    }

    public function index(Request $request): Response
    {
        // AMBIL ID DARI SESSION
        $activeStudentId = $request->session()->get('parent_portal_student_id');

        // KIRIM KE SERVICE
        $student = $this->dashboardService->resolveStudent($request->user(), $activeStudentId);

        abort_if(is_null($student), 404, 'Data siswa tidak ditemukan untuk akun ini.');

        $summary = $this->dashboardService->buildSummary($student);

        $notes = $this->resolveSimpleCollection(
            TeacherNoteResource::collection($this->dashboardService->latestNotes($student)),
            $request
        );

        $schedules = $this->resolveSimpleCollection(
            ScheduleResource::collection($this->dashboardService->upcomingSchedules($student)),
            $request
        );

        $attendances = $this->resolveSimpleCollection(
            AttendanceResource::collection($this->dashboardService->recentAttendances($student)),
            $request
        );

        return Inertia::render('OrangTua/Dashboard', [
            'student' => StudentResource::make($student),
            'summary' => $summary,
            'notes' => $notes,
            'schedules' => $schedules,
            'attendances' => $attendances,
        ]);
    }

    private function resolveSimpleCollection($collection, Request $request): array
    {
        $resolved = $collection->resolve($request);
        return $resolved['data'] ?? $resolved;
    }
}