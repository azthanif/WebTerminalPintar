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
		'schedule_id',
		'attendance_id',
		'teacher_id',
		'title',
		'note',
		'attachments',
		'follow_up_actions',
		'category',
		'tag_color',
		'sentiment',
		'is_flagged',
		'visibility',
		'recorded_at',
	];

	protected $casts = [
		'recorded_at' => 'datetime',
		'attachments' => 'array',
		'is_flagged' => 'boolean',
	];

	public function schedule(): BelongsTo
	{
		return $this->belongsTo(Schedule::class);
	}

	public function student(): BelongsTo
	{
		return $this->belongsTo(Student::class);
	}

	public function teacher(): BelongsTo
	{
		return $this->belongsTo(User::class, 'teacher_id');
	}

	public function attendance(): BelongsTo
	{
		return $this->belongsTo(Attendance::class);
	}
}

