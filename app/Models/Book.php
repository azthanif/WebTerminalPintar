<?php

namespace App\Models;

use App\Models\Loan;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Book extends Model
{
	use HasFactory;

	public const STATUS_AVAILABLE   = 'available';
	public const STATUS_BORROWED    = 'borrowed';
	public const STATUS_MAINTENANCE = 'maintenance';
	public const STATUS_LOST        = 'lost';

	protected $fillable = [
		'code',
		'title',
		'author',
		'category',
		'status',
		'published_year',
		'total_pages',
		'total_stock',
		'available_stock',
		'cover_image_path',
		'description',
	];

	protected $casts = [
		'published_year'  => 'integer',
		'total_pages'     => 'integer',
		'total_stock'     => 'integer',
		'available_stock' => 'integer',
	];

	public function loans(): HasMany
	{
		return $this->hasMany(Loan::class);
	}

	public function scopeAvailable($query)
	{
		return $query->where('status', self::STATUS_AVAILABLE);
	}

	public function getStatusLabelAttribute(): string
	{
		return match ($this->status) {
			self::STATUS_BORROWED    => 'Dipinjam',
			self::STATUS_MAINTENANCE => 'Perawatan',
			self::STATUS_LOST        => 'Hilang',
			default                  => 'Tersedia',
		};
	}
}

