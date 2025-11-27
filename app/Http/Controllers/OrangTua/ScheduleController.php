<?php

namespace App\Http\Controllers\OrangTua;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrangTua\ScheduleFilterRequest;
use App\Http\Resources\OrangTua\ScheduleResource;
use App\Http\Resources\OrangTua\StudentResource;
use App\Services\OrangTua\DashboardService;
use App\Services\OrangTua\ScheduleService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Inertia\Response;

class ScheduleController extends Controller
{
    public function __construct(
        private readonly DashboardService $dashboardService,
        private readonly ScheduleService $scheduleService
    ) {
    }

    public function index(ScheduleFilterRequest $request): Response
    {
        $student = $this->dashboardService->resolveStudent($request->user());

        abort_if(is_null($student), 404, 'Data siswa tidak ditemukan untuk akun ini.');

        $filters = $request->validated();
        $schedules = $this->scheduleService->list($student, $filters);

        return Inertia::render('OrangTua/Schedules', [
            'student' => StudentResource::make($student),
            'filters' => $filters,
            'schedules' => ScheduleResource::collection($schedules),
            'statusOptions' => ['scheduled', 'completed', 'canceled'],
        ]);
    }

    public function data(ScheduleFilterRequest $request): AnonymousResourceCollection
    {
        $student = $this->dashboardService->resolveStudent($request->user());

        abort_if(is_null($student), 404);

        return ScheduleResource::collection(
            $this->scheduleService->list($student, $request->validated())
        );
    }
}
