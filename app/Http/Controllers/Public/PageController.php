<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class PageController extends Controller
{
    public function about(): Response
    {
        return Inertia::render('Public/Static/About');
    }

    public function contact(): Response
    {
        return Inertia::render('Public/Static/Contact');
    }
}
