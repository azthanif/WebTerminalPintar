<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        // 'role_id',
        'name',
        'email',
        'phone',
        'password',
        'is_active',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    // public function role()
    // {
    //     return $this->belongsTo(Role::class);
    // }

    public function getNamaRoleAttribute()
    {
        return $this->getRoleNames()->first(); // "admin", "guru", "ortu"
    }

    // helper untuk badge status
    public function getStatusLabelAttribute(): string
    {
        return $this->is_active ? 'Aktif' : 'Nonaktif';
    }
}
