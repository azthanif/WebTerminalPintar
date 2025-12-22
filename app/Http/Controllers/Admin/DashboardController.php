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
		// Hitung pertumbuhan siswa bulan ini (ikuti total siswa yang ditampilkan di kartu)
		$thisMonthStudents = Student::whereBetween('created_at', [
			Carbon::now()->startOfMonth(),
			Carbon::now()->endOfMonth(),
		])->count();

		$lastMonthStudents = Student::whereBetween('created_at', [
			Carbon::now()->subMonth()->startOfMonth(),
			Carbon::now()->subMonth()->endOfMonth(),
		])->count();

		// Logika: jika ada siswa bulan lalu hitung persentase, jika tidak ada hitung dari 0 ke bulan ini (100%)
		if ($lastMonthStudents > 0) {
			$studentGrowthPercentage = round((($thisMonthStudents - $lastMonthStudents) / $lastMonthStudents) * 100);
		} else {
			$studentGrowthPercentage = $thisMonthStudents > 0 ? 100 : 0;
		}

		// Hitung artikel yang ditambah minggu ini secara dinamis
		$newsThisWeek = News::whereBetween('created_at', [
			Carbon::now()->startOfWeek(),
			Carbon::now()->endOfWeek(),
		])->count();

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
				'total'      => Student::count(),
				'active'     => Student::where('status', 'Aktif')->count(),
				'alumni'     => Student::where('status', '!=', 'Aktif')->count(),
				'growth'     => $studentGrowthPercentage,
			],
			'news' => [
				'total'      => News::count(),
				'published'  => News::where('is_published', true)->count(),
				'thisWeek'   => $newsThisWeek,
			],
			'books' => $bookStats,
		];

		$recentNews = News::query()
			->latest() // prioritize most recently created news for activity feed
			->take(5)
			->get(['id', 'title', 'event_date', 'is_published', 'created_at'])
			->map(fn ($news) => [
				'id'          => $news->id,
				'title'       => $news->title,
				'event_date'  => optional($news->event_date)->format('d M Y') ?? Carbon::parse($news->created_at)->format('d M Y'),
				'isPublished' => (bool) $news->is_published,
				'activity_at' => Carbon::parse($news->created_at)->diffForHumans(),
				'created_at'  => Carbon::parse($news->created_at)->toIso8601String(),
			])->values()->all();

		$recentUsers = User::query()
			->latest()
			->take(5)
			->get(['id', 'name', 'created_at'])
			->map(fn ($user) => [
				'id'         => $user->id,
				'name'       => $user->name,
				'joined_at'  => Carbon::parse($user->created_at)->diffForHumans(),
				'created_at' => Carbon::parse($user->created_at)->toIso8601String(),
			])->values()->all();

		$recentBooks = Book::query()
			->latest()
			->take(5)
			->get(['id', 'title', 'category', 'created_at'])
			->map(fn ($book) => [
				'id'        => $book->id,
				'title'     => $book->title,
				'category'  => $book->category,
				'added_at'  => Carbon::parse($book->created_at)->diffForHumans(),
				'created_at'=> Carbon::parse($book->created_at)->toIso8601String(),
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
				'created_at'      => Carbon::parse($student->created_at)->toIso8601String(),
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
			'recentUsers'    => $recentUsers,
			'recentBooks'    => $recentBooks,
			'recentStudents' => $recentStudents,
			'growth'         => $growth,
			'title'          => 'Dashboard Admin',
		]);
	}
}

