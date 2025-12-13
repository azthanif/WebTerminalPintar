<?php

use App\Http\Controllers\OrangTua\DashboardController;
use App\Http\Controllers\OrangTua\ScheduleController;
use App\Http\Controllers\OrangTua\StudentSwitcherController; // <--- Import Controller Baru
use App\Http\Controllers\OrangTua\TeacherNoteController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'role:ortu', 'parent.student'])
    ->prefix('orang-tua')
    ->name('orang-tua.')
    ->group(function () {
        
        // --- ROUTE BARU: GANTI SISWA ---
        Route::post('/switch-student', StudentSwitcherController::class)
            ->name('switch.student');
        // -------------------------------

        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->middleware('permission:parent.dashboard.view')
            ->name('dashboard');

        Route::get('/jadwal', [ScheduleController::class, 'index'])
            ->middleware('permission:parent.dashboard.schedules')
            ->name('schedules.index');
        Route::get('/jadwal/data', [ScheduleController::class, 'data'])
            ->middleware('permission:parent.dashboard.schedules')
            ->name('schedules.data');
        Route::get('/jadwal/calendar', [ScheduleController::class, 'calendar'])
            ->middleware('permission:parent.dashboard.schedules')
            ->name('schedules.calendar');

        Route::get('/catatan', [TeacherNoteController::class, 'index'])
            ->middleware('permission:parent.dashboard.notes')
            ->name('notes.index');
        Route::get('/catatan/data', [TeacherNoteController::class, 'data'])
            ->middleware('permission:parent.dashboard.notes')
            ->name('notes.data');
    });