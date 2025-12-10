<?php

namespace App\Models;

use App\Models\Attendance;
use App\Models\Schedule;
use App\Models\TeacherNote;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

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

    public function schedules(): BelongsToMany
    {
        return $this->belongsToMany(Schedule::class)
            ->withTimestamps();
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->status === 'active' ? 'Aktif' : 'Nonaktif';
    }

    public static function generateStudentId(): string
    {
        $lastNumber = self::withTrashed()
            ->whereRaw("student_id REGEXP '^SW[0-9]{3}$'")
            ->max(DB::raw('CAST(SUBSTRING(student_id, 3) AS UNSIGNED)')) ?? 0;

        $nextNumber = $lastNumber + 1;

        return 'SW' . str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT);
    }
}
