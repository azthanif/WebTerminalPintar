<?php

use App\Http\Controllers\Admin\{
    DashboardController,
    UserController,
    StudentController,
    NewsController,
    BookController,
    LoanController,
    LibraryCirculationController,
    ReportController,
    DonationController,
    SettingController
};
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\CaptchaController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\NewsController as PublicNewsController;
use App\Http\Controllers\Public\PageController;
use Illuminate\Foundation\Application;


// use App\Http\Controllers\Public\HomeController;
// use App\Http\Controllers\Public\NewsController;
// use App\Http\Controllers\Public\PageController;

// ================== HALAMAN PUBLIK ==================

Route::get('/', HomeController::class)->name('public.home');

Route::get('/berita', [PublicNewsController::class, 'index'])
    ->name('public.news.index');

Route::get('/berita/{slug}', [PublicNewsController::class, 'show'])
    ->name('public.news.show');

Route::get('/tentang-kami', [PageController::class, 'about'])
    ->name('public.about');

Route::get('/kontak', [PageController::class, 'contact'])
    ->name('public.contact');

Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('/', fn () => redirect()->route('admin.dashboard'))
            ->name('home');

        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->name('dashboard');

        Route::resource('users', UserController::class);
        // Route::get('students', [StudentController::class, 'index'])
        //     ->name('students.index');
        // // nanti bisa ditambah create/store/edit/update

        Route::resource('students', StudentController::class)->except(['show']);

        // Route::resource('news', NewsController::class);
        Route::resource('berita', NewsController::class)
            ->parameters(['berita' => 'news'])
            ->except(['show']);

        Route::resource('books', BookController::class)
            ->except(['show']);

        Route::resource('loans', LoanController::class)
            ->only(['index', 'store', 'update']);

        Route::prefix('perpustakaan')
            ->name('perpustakaan.')
            ->group(function () {
                Route::get('sirkulasi', [LibraryCirculationController::class, 'index'])
                    ->name('sirkulasi');
                Route::post('pinjam', [LibraryCirculationController::class, 'pinjam'])
                    ->name('pinjam');
                Route::post('kembalikan', [LibraryCirculationController::class, 'kembalikan'])
                    ->name('kembalikan');
            });

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

// Captcha
Route::get('/captcha/generate', [CaptchaController::class, 'generate'])->name('captcha.generate');
Route::post('/captcha/verify', [CaptchaController::class, 'verify'])->name('captcha.verify');

// Ganti Password (Pertama kali login)
Route::middleware('auth')->group(function () {
    Route::get('/change-password', [App\Http\Controllers\Auth\PasswordChangeController::class, 'show'])
        ->name('auth.change-password');
    Route::post('/change-password', [App\Http\Controllers\Auth\PasswordChangeController::class, 'update'])
        ->name('auth.change-password.update');
});

// Logout
Route::post('/logout', [LoginController::class, 'destroy'])
    ->name('logout');

require __DIR__ . '/guru.php';
require __DIR__ . '/orangtua.php';
