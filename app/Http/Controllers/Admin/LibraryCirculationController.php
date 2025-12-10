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
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class LibraryCirculationController extends Controller
{
    public function index(): Response
    {
        $availableBooks = Book::query()
            ->where('available_stock', '>', 0)
            ->orderBy('title')
            ->get(['id', 'title', 'category', 'code', 'status', 'available_stock']);

        $activeLoans = Loan::query()
            ->where('status', Loan::STATUS_BORROWED)
            ->with([
                'book:id,title,category,code,status',
                'borrower:id,name,email',
            ])
            ->orderByDesc('borrowed_at')
            ->get();

        $users = User::orderBy('name')->get(['id', 'name', 'email']);

        $bukuTersedia = $availableBooks->map(function (Book $book) {
            return [
                'id'             => $book->id,
                'judul'          => $book->title,
                'kategori'       => $book->category,
                'kode'           => $book->code,
                'status'         => $book->status,
                'status_label'   => $book->status_label,
                'availableStock' => $book->available_stock,
            ];
        })->values();

        $bukuDipinjam = $activeLoans->map(function (Loan $loan) {
            return [
                'id'          => $loan->book_id,
                'loan_id'     => $loan->id,
                'judul'       => $loan->book?->title,
                'kategori'    => $loan->book?->category,
                'kode'        => $loan->book?->code,
                'peminjaman'  => [[
                    'id'        => $loan->id,
                    'peminjam'  => [
                        'id'    => $loan->borrower?->id,
                        'nama'  => $loan->borrower?->name,
                        'email' => $loan->borrower?->email,
                    ],
                    'display_name'  => $loan->borrower_display_name,
                    'display_email' => $loan->borrower_display_email,
                    'tanggal_pinjam' => optional($loan->borrowed_at)->toDateString(),
                ]],
            ];
        })->values();

        $usersPayload = $users->map(function (User $user) {
            return [
                'id'    => $user->id,
                'nama'  => $user->name,
                'email' => $user->email,
            ];
        })->values();

        return Inertia::render('Admin/Sirkulasi', [
            'user'         => Auth::user(),
            'bukuTersedia' => $bukuTersedia,
            'bukuDipinjam' => $bukuDipinjam,
            'users'        => $usersPayload,
            'stats'        => [
                'available'  => $availableBooks->count(),
                'borrowed'   => $activeLoans->count(),
                'totalUsers' => $users->count(),
                'totalBooks' => Book::count(),
            ],
            'title'        => 'Sirkulasi Perpustakaan',
        ]);
    }

    public function pinjam(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'buku_id'         => ['required', 'exists:books,id'],
            'peminjam_id'     => ['nullable', 'exists:users,id', 'required_without:peminjam_nama'],
            'peminjam_nama'   => ['nullable', 'string', 'max:150', 'required_without:peminjam_id'],
            'peminjam_email'  => ['nullable', 'email', 'max:255'],
            'tanggal_pinjam'  => ['required', 'date'],
        ], [
            'buku_id.required'           => 'Buku wajib dipilih.',
            'peminjam_id.required_without'   => 'Pilih pengguna atau tulis nama peminjam.',
            'peminjam_nama.required_without' => 'Nama peminjam wajib diisi jika tidak memilih pengguna.',
            'tanggal_pinjam.required'    => 'Tanggal pinjam wajib diisi.',
        ]);

        DB::beginTransaction();

        try {
            $book = Book::lockForUpdate()->findOrFail($validated['buku_id']);

            if ($book->available_stock <= 0 || $book->status !== Book::STATUS_AVAILABLE) {
                throw ValidationException::withMessages([
                    'error' => 'Buku tidak tersedia untuk dipinjam.',
                ]);
            }

            $borrowerUserId = $validated['peminjam_id'] ?? null;
            $borrowerName   = $validated['peminjam_nama'] ?? null;
            $borrowerEmail  = $validated['peminjam_email'] ?? null;

            if ($borrowerUserId) {
                $user = User::select('id', 'name', 'email')->findOrFail($borrowerUserId);
                $borrowerName  = $borrowerName ?: $user->name;
                $borrowerEmail = $borrowerEmail ?: $user->email;
            }

            Loan::create([
                'book_id'     => $validated['buku_id'],
                'user_id'     => $borrowerUserId,
                'borrower_name'  => $borrowerName,
                'borrower_email' => $borrowerEmail,
                'issued_by'   => Auth::id(),
                'borrowed_at' => $validated['tanggal_pinjam'],
                'status'      => Loan::STATUS_BORROWED,
            ]);

            $book->available_stock = max(0, $book->available_stock - 1);
            if ($book->available_stock === 0) {
                $book->status = Book::STATUS_BORROWED;
            }
            $book->save();

            DB::commit();

            return redirect()
                ->route('admin.perpustakaan.sirkulasi')
                ->with('success', 'Peminjaman berhasil dicatat.');
        } catch (ValidationException $exception) {
            DB::rollBack();

            throw $exception;
        } catch (Throwable $throwable) {
            DB::rollBack();

            return back()
                ->withErrors(['error' => 'Gagal mencatat peminjaman: ' . $throwable->getMessage()])
                ->withInput();
        }
    }

    public function kembalikan(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'peminjaman_id'   => ['required', 'exists:loans,id'],
            'tanggal_kembali' => ['required', 'date'],
        ], [
            'peminjaman_id.required'   => 'Peminjaman wajib dipilih.',
            'tanggal_kembali.required' => 'Tanggal kembali wajib diisi.',
        ]);

        DB::beginTransaction();

        try {
            $loan = Loan::lockForUpdate()->with('book')->findOrFail($validated['peminjaman_id']);

            if ($loan->status === Loan::STATUS_RETURNED) {
                throw ValidationException::withMessages([
                    'error' => 'Buku sudah dikembalikan sebelumnya.',
                ]);
            }

            $loan->update([
                'returned_at' => $validated['tanggal_kembali'],
                'status'      => Loan::STATUS_RETURNED,
            ]);

            if ($loan->book) {
                $loan->book->available_stock = min($loan->book->available_stock + 1, $loan->book->total_stock);
                $loan->book->status          = Book::STATUS_AVAILABLE;
                $loan->book->save();
            }

            DB::commit();

            return redirect()
                ->route('admin.perpustakaan.sirkulasi')
                ->with('success', 'Pengembalian berhasil dicatat.');
        } catch (ValidationException $exception) {
            DB::rollBack();

            throw $exception;
        } catch (Throwable $throwable) {
            DB::rollBack();

            return back()
                ->withErrors(['error' => 'Gagal mencatat pengembalian: ' . $throwable->getMessage()])
                ->withInput();
        }
    }
}
