<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Loan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Arr;

class BookController extends Controller
{
	public function index(Request $request): Response
	{
		$filters = $request->only(['search', 'status']);

		$books = Book::query()
			->withCount('loans')
			->with(['activeLoans.borrower'])
			->when($filters['search'] ?? null, function ($query, $search) {
				$query->where(function ($q) use ($search) {
					$q->where('title', 'like', "%{$search}%")
						->orWhere('author', 'like', "%{$search}%")
						->orWhere('code', 'like', "%{$search}%");
				});
			})
			->when($filters['status'] ?? null, fn($query, $status) => $query->where('status', $status))
			->orderBy('title')
			->paginate(10)
			->withQueryString();

		$stats = [
			'total'       => Book::count(),
			'available'   => Book::where('status', Book::STATUS_AVAILABLE)->count(),
			'borrowed'    => Book::where('status', Book::STATUS_BORROWED)->count(),
			'maintenance' => Book::where('status', Book::STATUS_MAINTENANCE)->count(),
			'lost'        => Book::where('status', Book::STATUS_LOST)->count(),
		];

		$this->attachBorrowerMeta($books);

		return Inertia::render('Admin/Books/Index', [
			'books'      => $books,
			'stats'      => $stats,
			'filters'    => $filters,
			'statuses'   => $this->statuses(),
			'categories' => $this->categories(),
			'title'      => 'Kelola Perpustakaan',
		]);
	}

	public function create(): Response
	{
		return Inertia::render('Admin/Books/Create', [
			'book'       => null,
			'statuses'   => $this->createFormStatuses(),
			'categories' => $this->categories(),
			'title'      => 'Tambah Buku',
		]);
	}

	public function store(Request $request): RedirectResponse
	{
		$data = $this->validateData($request);
		$data['available_stock'] = $data['available_stock'] ?? $data['total_stock'];
		$data['available_stock'] = min($data['available_stock'], $data['total_stock']);

		Book::create($data);

		return redirect()
			->route('admin.books.index')
			->with('success', 'Buku berhasil ditambahkan.');
	}

	public function edit(Book $book): Response
	{
		return Inertia::render('Admin/Books/Edit', [
			'book'       => $book,
			'statuses'   => $this->editFormStatuses($book),
			'categories' => $this->categories(),
			'title'      => 'Ubah Data Buku',
		]);
	}

	public function update(Request $request, Book $book): RedirectResponse
	{
		$data = $this->validateData($request, $book->id);
		$data['available_stock'] = $data['available_stock'] ?? $book->available_stock;
		$data['available_stock'] = min($data['available_stock'], $data['total_stock']);

		$book->update($data);

		return redirect()
			->route('admin.books.index')
			->with('success', 'Data buku berhasil diperbarui.');
	}

	public function destroy(Book $book): RedirectResponse
	{
		$book->delete();

		return redirect()
			->route('admin.books.index')
			->with('success', 'Buku berhasil dihapus.');
	}

	private function validateData(Request $request, ?int $bookId = null): array
	{
		return $request->validate([
			'code'            => ['nullable', 'string', 'max:30', Rule::unique('books', 'code')->ignore($bookId)],
			'title'           => ['required', 'string', 'max:255'],
			'author'          => ['nullable', 'string', 'max:150'],
			'category'        => ['nullable', 'string', 'max:100'],
			'status'          => ['required', Rule::in(array_keys($this->statuses()))],
			'published_year'  => ['nullable', 'integer', 'digits:4'],
			'total_pages'     => ['nullable', 'integer', 'min:1'],
			'total_stock'     => ['required', 'integer', 'min:1'],
			'available_stock' => ['nullable', 'integer', 'min:0', 'lte:total_stock'],
			'description'     => ['nullable', 'string'],
		]);
	}

	private function statuses(): array
	{
		return [
			Book::STATUS_AVAILABLE   => 'Tersedia',
			Book::STATUS_BORROWED    => 'Dipinjam',
			Book::STATUS_MAINTENANCE => 'Perawatan',
			Book::STATUS_LOST        => 'Hilang',
		];
	}

	private function createFormStatuses(): array
	{
		return Arr::except($this->statuses(), [Book::STATUS_BORROWED]);
	}

	private function editFormStatuses(Book $book): array
	{
		$statuses = Arr::only($this->statuses(), [Book::STATUS_MAINTENANCE, Book::STATUS_LOST]);

		return $statuses;
	}

	private function categories(): array
	{
		return [
			'Umum',
			'Kurikulum',
			'Pengayaan',
			'Fiksi',
			'Non Fiksi',
		];
	}

	private function attachBorrowerMeta(LengthAwarePaginator $books): void
	{
		$activeLoans = collect($books->items())
			->flatMap(fn(Book $book) => $book->activeLoans)
			->filter()
			->values();

		if ($activeLoans->isEmpty()) {
			return;
		}

		$uniqueBorrowers = $activeLoans->unique(function ($loan) {
			return $loan->user_id
				? 'user-' . $loan->user_id
				: 'guest-' . $loan->borrower_name . '-' . ($loan->borrower_email ?? '');
		});

		$counts = Loan::query()
			->where('status', Loan::STATUS_BORROWED)
			->where(function ($query) use ($uniqueBorrowers) {
				foreach ($uniqueBorrowers as $loan) {
					$query->orWhere(function ($subQuery) use ($loan) {
						if ($loan->user_id) {
							$subQuery->where('user_id', $loan->user_id);
						} else {
							$subQuery->whereNull('user_id')
								->where('borrower_name', $loan->borrower_name)
								->when(
									$loan->borrower_email,
									fn($q) => $q->where('borrower_email', $loan->borrower_email),
									fn($q) => $q->whereNull('borrower_email')
								);
						}
					});
				}
			})
			->select('user_id', 'borrower_name', 'borrower_email', DB::raw('COUNT(*) as total'))
			->groupBy('user_id', 'borrower_name', 'borrower_email')
			->get();

		$books->setCollection($books->getCollection()->map(function (Book $book) use ($counts) {
			$book->setRelation('activeLoans', $book->activeLoans->map(function (Loan $loan) use ($counts) {
				$loan->borrowed_books_total = $this->resolveBorrowCount($counts, $loan);

				return $loan;
			}));

			return $book;
		}));
	}

	private function resolveBorrowCount(Collection $counts, Loan $loan): int
	{
		$match = $counts->first(function ($item) use ($loan) {
			if ($loan->user_id) {
				return (int) $item->user_id === (int) $loan->user_id;
			}

			if (!is_null($item->user_id)) {
				return false;
			}

			$emailMatches = $loan->borrower_email
				? $item->borrower_email === $loan->borrower_email
				: is_null($item->borrower_email);

			return $item->borrower_name === $loan->borrower_name && $emailMatches;
		});

		return (int) ($match->total ?? 1);
	}
}

