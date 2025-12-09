<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Material extends Model
{
    use HasFactory;

    protected $fillable = [
        'schedule_id',
        'title',
        'description',
        'file_path',
        'file_type',
        'file_size',
        'download_url',
        'status',
        'downloads_count',
        'uploaded_by',
        'visibility',
        'expires_at',
        'labels',
        'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'expires_at' => 'datetime',
        'labels' => 'array',
    ];

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function incrementDownloads(): void
    {
        $this->increment('downloads_count');
    }
}
