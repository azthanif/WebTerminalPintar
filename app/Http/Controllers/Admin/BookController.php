<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class BookController extends Controller
{
	public function index(Request $request): Response
	{
		$filters = $request->only(['search', 'status']);

		$books = Book::query()
			->withCount('loans')
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
			'statuses'   => $this->statuses(),
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
			'statuses'   => $this->statuses(),
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
}

