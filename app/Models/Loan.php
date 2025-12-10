<?php

namespace App\Models;

use App\Models\Book;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Loan extends Model
{
	use HasFactory, SoftDeletes;

	public const STATUS_BORROWED = 'borrowed';
	public const STATUS_RETURNED = 'returned';
	public const STATUS_OVERDUE  = 'overdue';

	protected $fillable = [
		'book_id',
		'user_id',
		'issued_by',
		'borrowed_at',
		'due_at',
		'returned_at',
		'status',
		'notes',
		'borrower_name',
		'borrower_email',
	];

	protected $appends = [
		'borrower_display_name',
		'borrower_display_email',
	];

	protected $casts = [
		'borrowed_at' => 'date',
		'due_at'      => 'date',
		'returned_at' => 'date',
	];

	public function book(): BelongsTo
	{
		return $this->belongsTo(Book::class);
	}

	public function borrower(): BelongsTo
	{
		return $this->belongsTo(User::class, 'user_id');
	}

	public function issuedBy(): BelongsTo
	{
		return $this->belongsTo(User::class, 'issued_by');
	}

	public function scopeActive($query)
	{
		return $query->whereNull('returned_at');
	}

	public function getStatusLabelAttribute(): string
	{
		return match ($this->status) {
			self::STATUS_RETURNED => 'Dikembalikan',
			self::STATUS_OVERDUE  => 'Terlambat',
			default               => 'Dipinjam',
		};
	}

	public function getBorrowerDisplayNameAttribute(): ?string
	{
		return $this->borrower?->name ?? $this->borrower_name;
	}

	public function getBorrowerDisplayEmailAttribute(): ?string
	{
		return $this->borrower?->email ?? $this->borrower_email;
	}
}

