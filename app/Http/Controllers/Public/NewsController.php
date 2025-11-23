<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\Public\NewsPublicResource;
use App\Services\Public\NewsPublicService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NewsController extends Controller
{
    public function __construct(
        protected NewsPublicService $newsPublicService,
    ) {}

    /**
     * /berita – daftar berita publik (paginated)
     */
    public function index(Request $request): Response
    {
        $filters = $request->only('search');

        $paginator = $this->newsPublicService->paginatePublicNews($filters, 6);

        $beritaData = [
            'data'         => NewsPublicResource::collection($paginator->items())->resolve(),
            'current_page' => $paginator->currentPage(),
            'last_page'    => $paginator->lastPage(),
            'per_page'     => $paginator->perPage(),
            'total'        => $paginator->total(),
            'from'         => $paginator->firstItem(),
            'to'           => $paginator->lastItem(),
        ];

        return Inertia::render('Public/News/Index', [
            'berita'  => $beritaData,
            'filters' => $filters,
        ]);
    }

    /**
     * /berita/{slug} – detail berita publik
     */
    public function show(string $slug): Response
    {
        $news = $this->newsPublicService->findBySlug($slug);

        return Inertia::render('Public/News/Show', [
            'berita' => new NewsPublicResource($news),
        ]);
    }
}
