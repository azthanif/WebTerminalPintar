<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TeacherNote extends Model
{
	use HasFactory, SoftDeletes;

	protected $fillable = [
		'student_id',
		'teacher_id',
		'note',
		'recorded_at',
	];

	protected $casts = [
		'recorded_at' => 'datetime',
	];

	public function student(): BelongsTo
	{
		return $this->belongsTo(Student::class);
	}

	public function teacher(): BelongsTo
	{
		return $this->belongsTo(User::class, 'teacher_id');
	}
}

