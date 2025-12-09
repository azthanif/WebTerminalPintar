<?php

use App\Http\Controllers\Guru\AttendanceController;
use App\Http\Controllers\Guru\AttendancePageController;
use App\Http\Controllers\Guru\DashboardController;
use App\Http\Controllers\Guru\MaterialController;
use App\Http\Controllers\Guru\NoteController;
use App\Http\Controllers\Guru\ScheduleApiController;
use App\Http\Controllers\Guru\ScheduleController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'role:guru'])
    ->prefix('guru')
    ->name('guru.')
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->name('dashboard');

        Route::get('/jadwal', [ScheduleController::class, 'index'])
            ->name('schedules');

        Route::get('/kehadiran', AttendancePageController::class)
            ->name('attendance');

        Route::prefix('api')->name('api.')->group(function () {
            Route::get('/dashboard/summary', [DashboardController::class, 'summary'])
                ->name('dashboard.summary');

            Route::get('/schedules', [ScheduleApiController::class, 'index'])
                ->name('schedules.index');
            Route::post('/schedules', [ScheduleApiController::class, 'store'])
                ->name('schedules.store');
            Route::put('/schedules/{schedule}', [ScheduleApiController::class, 'update'])
                ->name('schedules.update');
            Route::delete('/schedules/{schedule}', [ScheduleApiController::class, 'destroy'])
                ->name('schedules.destroy');
            Route::post('/schedules/{schedule}/restore', [ScheduleApiController::class, 'restore'])
                ->name('schedules.restore');

            Route::post('/schedules/{schedule}/materials', [MaterialController::class, 'store'])
                ->name('materials.store');
            Route::put('/materials/{material}', [MaterialController::class, 'update'])
                ->name('materials.update');
            Route::delete('/materials/{material}', [MaterialController::class, 'destroy'])
                ->name('materials.destroy');

            Route::get('/attendance', [AttendanceController::class, 'index'])
                ->name('attendance.index');
            Route::post('/attendance', [AttendanceController::class, 'store'])
                ->name('attendance.store');
            Route::delete('/attendance/{attendance}', [AttendanceController::class, 'destroy'])
                ->name('attendance.destroy');

            Route::get('/notes', [NoteController::class, 'list'])
                ->name('notes.index');
            Route::post('/notes', [NoteController::class, 'store'])
                ->name('notes.store');
            Route::put('/notes/{teacherNote}', [NoteController::class, 'update'])
                ->name('notes.update');
            Route::delete('/notes/{teacherNote}', [NoteController::class, 'destroy'])
                ->name('notes.destroy');
        });
    });
