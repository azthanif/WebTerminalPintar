<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class LoanController extends Controller
{
	public function index(Request $request): Response
	{
		$filters = $request->only(['status', 'search']);

		$loans = Loan::query()
			->with(['book', 'borrower'])
			->when($filters['status'] ?? null, fn($query, $status) => $query->where('status', $status))
			->when($filters['search'] ?? null, function ($query, $search) {
				$query->where(function ($relationQuery) use ($search) {
					$relationQuery
						->whereHas('book', fn($q) => $q->where('title', 'like', "%{$search}%"))
						->orWhereHas('borrower', fn($q) => $q->where('name', 'like', "%{$search}%"));
				});
			})
			->orderByDesc('borrowed_at')
			->paginate(10)
			->withQueryString();

		$stats = [
			'active'   => Loan::where('status', Loan::STATUS_BORROWED)->count(),
			'overdue'  => Loan::where('status', Loan::STATUS_OVERDUE)->count(),
			'returned' => Loan::where('status', Loan::STATUS_RETURNED)->count(),
		];

		return Inertia::render('Admin/Loans/Index', [
			'loans'     => $loans,
			'stats'     => $stats,
			'filters'   => $filters,
			'books'     => Book::available()
				->where('available_stock', '>', 0)
				->orderBy('title')
				->get(['id', 'title', 'code']),
			'borrowers' => User::orderBy('name')->get(['id', 'name', 'email']),
			'statuses'  => $this->statuses(),
			'title'     => 'Sirkulasi Perpustakaan',
		]);
	}

	public function store(Request $request): RedirectResponse
	{
		$data = $request->validate([
			'book_id'     => ['required', 'exists:books,id'],
			'user_id'     => ['required', 'exists:users,id'],
			'borrowed_at' => ['required', 'date'],
			'due_at'      => ['nullable', 'date', 'after_or_equal:borrowed_at'],
			'notes'       => ['nullable', 'string'],
		]);

		DB::transaction(function () use ($data) {
			$book = Book::lockForUpdate()->findOrFail($data['book_id']);

			if ($book->available_stock <= 0 || $book->status !== Book::STATUS_AVAILABLE) {
				throw ValidationException::withMessages([
					'book_id' => 'Buku tidak tersedia untuk dipinjam.',
				]);
			}

			Loan::create([
				...$data,
				'issued_by' => Auth::id(),
				'status'    => Loan::STATUS_BORROWED,
			]);

			$book->available_stock = max(0, $book->available_stock - 1);
			if ($book->available_stock === 0) {
				$book->status = Book::STATUS_BORROWED;
			}
			$book->save();
		});

		return redirect()
			->route('admin.loans.index')
			->with('success', 'Peminjaman berhasil dicatat.');
	}

	public function update(Request $request, Loan $loan): RedirectResponse
	{
		$data = $request->validate([
			'status'      => ['required', Rule::in([Loan::STATUS_RETURNED, Loan::STATUS_OVERDUE])],
			'returned_at' => ['nullable', 'date'],
			'notes'       => ['nullable', 'string'],
		]);

		DB::transaction(function () use ($data, $loan) {
			if ($data['status'] === Loan::STATUS_RETURNED) {
				$loan->returned_at = $data['returned_at'] ?? now();
				$loan->status      = Loan::STATUS_RETURNED;
				$loan->notes       = $data['notes'] ?? $loan->notes;
				$loan->save();

				$book = Book::lockForUpdate()->findOrFail($loan->book_id);
				$book->available_stock = min($book->available_stock + 1, $book->total_stock);
				$book->status          = Book::STATUS_AVAILABLE;
				$book->save();
			} else {
				$loan->update([
					'status' => Loan::STATUS_OVERDUE,
					'notes'  => $data['notes'] ?? $loan->notes,
				]);
			}
		});

		return redirect()
			->route('admin.loans.index')
			->with('success', 'Status peminjaman berhasil diperbarui.');
	}

	private function statuses(): array
	{
		return [
			Loan::STATUS_BORROWED => 'Dipinjam',
			Loan::STATUS_OVERDUE  => 'Terlambat',
			Loan::STATUS_RETURNED => 'Dikembalikan',
		];
	}
}

