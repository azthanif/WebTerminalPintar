<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'title',
        'subtitle',
        'slug',
        'excerpt',
        'body',
        'cover_image_path',
        'second_image_path',
        'type',
        'event_date',
        'is_published',
    ];

    protected $casts = [
        'event_date'   => 'date',
        'is_published' => 'boolean',
    ];

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function penulis()
    {
        return $this->admin();
    }
}
