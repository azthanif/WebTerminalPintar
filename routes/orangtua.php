<?php

use App\Http\Controllers\OrangTua\DashboardController;
use App\Http\Controllers\OrangTua\ScheduleController;
use App\Http\Controllers\OrangTua\TeacherNoteController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'role:ortu', 'parent.student'])
    ->prefix('orang-tua')
    ->name('orang-tua.')
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->middleware('permission:parent.dashboard.view')
            ->name('dashboard');

        Route::get('/jadwal', [ScheduleController::class, 'index'])
            ->middleware('permission:parent.dashboard.schedules')
            ->name('schedules.index');
        Route::get('/jadwal/data', [ScheduleController::class, 'data'])
            ->middleware('permission:parent.dashboard.schedules')
            ->name('schedules.data');

        Route::get('/catatan', [TeacherNoteController::class, 'index'])
            ->middleware('permission:parent.dashboard.notes')
            ->name('notes.index');
        Route::get('/catatan/data', [TeacherNoteController::class, 'data'])
            ->middleware('permission:parent.dashboard.notes')
            ->name('notes.data');
    });
