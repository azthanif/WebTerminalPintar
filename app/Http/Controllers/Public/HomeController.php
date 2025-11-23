<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\Public\NewsPublicResource;
use App\Services\Public\HomePublicService;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __construct(
        protected HomePublicService $homePublicService,
    ) {}

    public function __invoke(): Response
    {
        $latestActivities = $this->homePublicService->getLatestActivities(3);

        return Inertia::render('Public/Home', [
            'kegiatan' => NewsPublicResource::collection($latestActivities)->resolve(),
        ]);
    }
}
