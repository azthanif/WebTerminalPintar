<?php

namespace App\Http\Controllers\OrangTua;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrangTua\ScheduleFilterRequest;
use App\Http\Resources\OrangTua\ScheduleResource;
use App\Http\Resources\OrangTua\StudentResource;
use App\Services\OrangTua\DashboardService;
use App\Services\OrangTua\ScheduleService;
use Carbon\Carbon;
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
        // AMBIL ID DARI SESSION
        $activeStudentId = $request->session()->get('parent_portal_student_id');

        // KIRIM KE SERVICE
        $student = $this->dashboardService->resolveStudent($request->user(), $activeStudentId);

        abort_if(is_null($student), 404, 'Data siswa tidak ditemukan untuk akun ini.');

        $filters = $request->validated();
        $schedules = $this->scheduleService->list($student, $filters);

        $calendarRange = $this->determineCalendarRange($filters);
        $calendarFilters = array_merge($filters, [
            'start_date' => $calendarRange['start']->toDateString(),
            'end_date' => $calendarRange['end']->toDateString(),
        ]);
        $calendar = $this->scheduleService->calendar($student, $calendarFilters);

        return Inertia::render('OrangTua/Schedules', [
            'student' => StudentResource::make($student),
            'filters' => $filters,
            'schedules' => ScheduleResource::collection($schedules),
            'statusOptions' => ['scheduled', 'completed', 'canceled'],
            'calendar' => ScheduleResource::collection($calendar),
            'calendarRange' => [
                'start_date' => $calendarRange['start']->toDateString(),
                'end_date' => $calendarRange['end']->toDateString(),
            ],
        ]);
    }

    public function data(ScheduleFilterRequest $request): AnonymousResourceCollection
    {
        // JANGAN LUPA UPDATE DI SINI JUGA (untuk API/Pagination)
        $activeStudentId = $request->session()->get('parent_portal_student_id');
        $student = $this->dashboardService->resolveStudent($request->user(), $activeStudentId);

        abort_if(is_null($student), 404);

        return ScheduleResource::collection(
            $this->scheduleService->list($student, $request->validated())
        );
    }

    public function calendar(ScheduleFilterRequest $request): AnonymousResourceCollection
    {
        $activeStudentId = $request->session()->get('parent_portal_student_id');
        $student = $this->dashboardService->resolveStudent($request->user(), $activeStudentId);

        abort_if(is_null($student), 404);

        $filters = $request->validated();
        $calendarRange = $this->determineCalendarRange($filters);

        $filters['start_date'] = $calendarRange['start']->toDateString();
        $filters['end_date'] = $calendarRange['end']->toDateString();

        return ScheduleResource::collection(
            $this->scheduleService->calendar($student, $filters)
        );
    }

    protected function determineCalendarRange(array $filters = []): array
    {
        $start = isset($filters['start_date'])
            ? Carbon::parse($filters['start_date'])->startOfDay()
            : now()->startOfWeek();

        $end = isset($filters['end_date'])
            ? Carbon::parse($filters['end_date'])->endOfDay()
            : now()->endOfWeek();

        return [
            'start' => $start,
            'end' => $end,
        ];
    }
}