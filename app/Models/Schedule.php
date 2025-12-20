<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Schedule extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'student_id',
        'teacher_id',
        'subject',
        'topic',
        'learning_focus',
        'description',
        'start_time',
        'end_time',
        'location',
        'max_participants',
        'present_count',
        'meeting_url',
        'attachments_meta',
        'color_hex',
        'learning_summary',
        'status_badge',
        'status_locked_at',
        'status',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'status_locked_at' => 'datetime',
        'attachments_meta' => 'array',
    ];

    public function scopeForTeacher($query, User $teacher)
    {
        return $query->where('teacher_id', $teacher->id);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('start_time', '>=', now())->orderBy('start_time');
    }

    public function refreshStatusBadge(): void
    {
        if ($this->status_locked_at) {
            return;
        }

        $now = Carbon::now();
        $status = match (true) {
            $this->start_time && $this->start_time->isFuture() => 'Akan Datang',
            $this->start_time && $this->end_time && $this->start_time->isPast() && $this->end_time->isFuture() => 'Berlangsung',
            $this->end_time && $this->end_time->isPast() => 'Selesai',
            default => $this->status_badge ?? 'Akan Datang',
        };

        $attributes = [
            'status_badge' => $status,
            'status' => $status,
        ];

        if ($this->getConnection()->getDriverName() === 'sqlite') {
            unset($attributes['status']);
        }

        $this->forceFill($attributes)->saveQuietly();
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status_badge) {
            'Berlangsung', 'ongoing' => '#10b981',
            'Selesai', 'completed' => '#0ea5e9',
            'Dibatalkan', 'canceled' => '#ef4444',
            default => '#f97316', // Akan Datang / upcoming
        };
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class)
            ->withTimestamps();
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function materials(): HasMany
    {
        return $this->hasMany(Material::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }
}
