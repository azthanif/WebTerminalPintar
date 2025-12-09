<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Arr;

class Attendance extends Model
{
	use HasFactory;

	public const STATUS_BADGES = [
		'Hadir' => '#16a34a',
		'Sakit' => '#0ea5e9',
		'Izin' => '#f97316',
		'Alpha' => '#ef4444',
	];

	protected $fillable = [
		'student_id',
		'schedule_id',
		'recorded_by',
		'attendance_date',
		'recorded_at',
		'status',
		'session_topic',
		'session_time',
		'input_channel',
		'notes',
		'requires_follow_up',
		'meta',
	];

	protected $casts = [
		'attendance_date' => 'date',
		'recorded_at' => 'datetime',
		'requires_follow_up' => 'boolean',
		'meta' => 'array',
	];

	public function scopeForTeacher($query, User $teacher)
	{
		return $query->whereHas('schedule', fn ($q) => $q->where('teacher_id', $teacher->id));
	}

	public function getStatusColorAttribute(): string
	{
		return Arr::get(self::STATUS_BADGES, $this->status, '#6b7280');
	}

	public function student(): BelongsTo
	{
		return $this->belongsTo(Student::class);
	}

	public function schedule(): BelongsTo
	{
		return $this->belongsTo(Schedule::class);
	}

	public function recordedBy(): BelongsTo
	{
		return $this->belongsTo(User::class, 'recorded_by');
	}
}

