<?php

namespace App\Services\Public;

use App\Models\News;
use App\Models\Student;
use Illuminate\Support\Collection;

class HomePublicService
{
    /**
     * Ambil beberapa berita/kegiatan terbaru untuk landing.
     */
    public function getLatestActivities(int $limit = 3): Collection
    {
        return News::query()
            ->orderByDesc('event_date')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    public function getHeroStats(): array
    {
        return [
            'students_count' => Student::count(),
            'news_count' => News::count(),
        ];
    }
}
