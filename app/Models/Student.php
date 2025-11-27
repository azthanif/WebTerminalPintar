<?php

namespace App\Models;

use App\Models\Attendance;
use App\Models\Schedule;
use App\Models\TeacherNote;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'parent_id',
        'student_id',
        'name',
        'education_level',
        'status',
        'date_of_birth',
        'school_name',
        'address',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    public function orangTua(): BelongsTo
    {
        return $this->parent();
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function teacherNotes(): HasMany
    {
        return $this->hasMany(TeacherNote::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->status === 'active' ? 'Aktif' : 'Nonaktif';
    }

    public static function generateStudentId(): string
    {
        $latest = self::withTrashed()
            ->where('student_id', 'like', 'SW%')
            ->orderByDesc('student_id')
            ->first();

        $lastNumber = 0;

        if ($latest && preg_match('/SW(\d{3})$/', $latest->student_id, $matches)) {
            $lastNumber = (int) $matches[1];
        }

        $nextNumber = $lastNumber + 1;

        return 'SW' . str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT);
    }
}
