<?php

namespace App\Services\Public;

use App\Models\News;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class NewsPublicService
{
    public function paginatePublicNews(array $filters, int $perPage = 6): LengthAwarePaginator
    {
        $query = News::query();

        if (!empty($filters['search'])) {
            $search = $filters['search'];

            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('excerpt', 'like', "%{$search}%")
                  ->orWhere('body', 'like', "%{$search}%");
            });
        }

        return $query
            ->orderByDesc('event_date')
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findBySlug(string $slug): News
    {
        return News::where('slug', $slug)->firstOrFail();
    }
}
