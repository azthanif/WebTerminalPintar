<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\News;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;

class DashboardController extends Controller
{
	public function index()
	{
		$bookStats = [
			'total'      => Schema::hasTable('books') ? Book::count() : 0,
			'categories' => Schema::hasTable('books')
				? Book::query()->whereNotNull('category')->distinct('category')->count('category')
				: 0,
		];

		$stats = [
			'users' => [
				'total'  => User::count(),
				'active' => User::where('is_active', true)->count(),
			],
			'students' => [
				'total'   => Student::count(),
				'active'  => Student::where('status', 'Aktif')->count(),
				'alumni'  => Student::where('status', '!=', 'Aktif')->count(),
			],
			'news' => [
				'total'     => News::count(),
				'published' => News::where('is_published', true)->count(),
			],
			'books' => $bookStats,
		];

		$recentNews = News::query()
			->latest('event_date')
			->latest()
			->take(5)
			->get(['id', 'title', 'event_date', 'is_published', 'created_at'])
			->map(fn ($news) => [
				'id'          => $news->id,
				'title'       => $news->title,
				'event_date'  => optional($news->event_date)->format('d M Y') ?? Carbon::parse($news->created_at)->format('d M Y'),
				'isPublished' => (bool) $news->is_published,
				'activity_at' => Carbon::parse($news->created_at)->diffForHumans(),
			])->values()->all();

		$recentStudents = Student::query()
			->latest()
			->take(5)
			->get(['id', 'student_id', 'name', 'education_level', 'status', 'created_at'])
			->map(fn ($student) => [
				'id'              => $student->id,
				'student_id'      => $student->student_id,
				'name'            => $student->name,
				'education_level' => $student->education_level,
				'status'          => $student->status,
				'joined_at'       => Carbon::parse($student->created_at)->diffForHumans(),
			])->values()->all();

		$growth = [
			'labels' => collect(range(5, 0, -1))->map(function ($monthsAgo) {
				return Carbon::now()->subMonths($monthsAgo)->shortMonthName;
			})->values()->all(),
			'users' => collect(range(5, 0, -1))->map(function ($monthsAgo) {
				$start = Carbon::now()->subMonths($monthsAgo)->startOfMonth();
				$end   = Carbon::now()->subMonths($monthsAgo)->endOfMonth();

				return User::whereBetween('created_at', [$start, $end])->count();
			})->values()->all(),
			'students' => collect(range(5, 0, -1))->map(function ($monthsAgo) {
				$start = Carbon::now()->subMonths($monthsAgo)->startOfMonth();
				$end   = Carbon::now()->subMonths($monthsAgo)->endOfMonth();

				return Student::whereBetween('created_at', [$start, $end])->count();
			})->values()->all(),
		];

		return Inertia::render('Admin/Dashboard/Index', [
			'stats'          => $stats,
			'recentNews'     => $recentNews,
			'recentStudents' => $recentStudents,
			'growth'         => $growth,
			'title'          => 'Dashboard Admin',
		]);
	}
}

