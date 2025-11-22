<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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

    public function parent()
    {
        return $this->belongsTo(User::class, 'parent_id');
    }
}
