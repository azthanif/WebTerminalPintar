<?php

namespace App\Models;

use App\Models\Attendance;
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

    public function getStatusLabelAttribute(): string
    {
        return $this->status === 'active' ? 'Aktif' : 'Nonaktif';
    }
}
