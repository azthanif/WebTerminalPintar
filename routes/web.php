<?php

use App\Http\Controllers\Admin\{
    DashboardController,
    UserController,
    StudentController,
    NewsController,
    BookController,
    LoanController,
    ReportController,
    DonationController,
    SettingController
};
use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        // Route::get('/dashboard', [DashboardController::class, 'index'])
        // ->name('dashboard');

        Route::resource('users', UserController::class);
        // Route::get('students', [StudentController::class, 'index'])
        //     ->name('students.index');
        // // nanti bisa ditambah create/store/edit/update

        Route::resource('students', StudentController::class)->except(['show']);

        // Route::resource('news', NewsController::class);
        Route::resource('berita', NewsController::class)
            ->parameters(['berita' => 'news'])
            ->except(['show']);

        // Route::resource('books', BookController::class);

        // Route::resource('loans', LoanController::class)
        // ->only(['index', 'store', 'update']);

        // Route::get('reports/attendance', [ReportController::class, 'attendance'])
        // ->name('reports.attendance');

        // Route::get('reports/library', [ReportController::class, 'library'])
        // ->name('reports.library');

        // Route::resource('donations', DonationController::class)
        // ->only(['index', 'store']);

        // Route::get('settings', [SettingController::class, 'index'])
        //     ->name('settings.index');
        // Route::post('settings', [SettingController::class, 'store'])
        //     ->name('settings.store');
    });

// Halaman login
Route::get('/login', [LoginController::class, 'create'])
    ->name('login');

// Proses login
Route::post('/login', [LoginController::class, 'store']);

// Logout
Route::post('/logout', [LoginController::class, 'destroy'])
    ->name('logout');
