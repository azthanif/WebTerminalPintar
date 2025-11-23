<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\News\StoreNewsRequest;
use App\Http\Requests\Admin\News\UpdateNewsRequest;
use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class NewsController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $query = News::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('id', $search)
                  ->orWhere('title', 'like', "%{$search}%");
            });
        }

        $berita = $query
            ->orderByDesc('event_date')
            ->orderByDesc('created_at')
            ->paginate(8)
            ->withQueryString();

        // transform ke shape yang dipakai Vue (judul, konten, created_at)
        $berita->getCollection()->transform(function (News $item) {
            return [
                'id'         => $item->id,
                'judul'      => $item->title,
                'konten'     => $item->excerpt ?: Str::limit(strip_tags($item->body), 160),
                'created_at' => ($item->event_date ?? $item->created_at)->toDateString(),
            ];
        });

        $stats = [
            'total_berita' => News::count(),
            'total_foto'   => News::whereNotNull('cover_image_path')->count(),
        ];

        return Inertia::render('Admin/News/Index', [
            'berita'  => $berita,
            'stats'   => $stats,
            'filters' => [
                'search' => $search,
            ],
            'title'   => 'Berita & Dokumentasi',
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/News/Form', [
            'news' => null,
        ]);
    }

    public function store(StoreNewsRequest $request)
    {
        $data = $request->validated();

        $news = new News();
        $news->admin_id  = $request->user()->id;
        $news->title     = $data['judul'];
        $news->slug      = Str::slug($data['judul']) . '-' . Str::random(4);
        $news->excerpt   = Str::limit(strip_tags($data['konten']), 160);
        $news->body      = $data['konten'];
        $news->event_date = $data['event_date'];
        $news->type      = $data['type'];

        $news->save();

        return redirect()
            ->route('admin.berita.index')
            ->with('success', 'Berita berhasil ditambahkan.');
    }

    public function edit(News $news)
    {
        return Inertia::render('Admin/News/Form', [
            'news' => [
                'id'         => $news->id,
                'judul'      => $news->title,
                'konten'     => $news->body,
                'event_date' => optional($news->event_date)->format('Y-m-d'),
                'type'       => $news->type,
            ],
        ]);
    }

    public function update(UpdateNewsRequest $request, News $news)
    {
        $data = $request->validated();

        $news->title      = $data['judul'];
        $news->excerpt    = Str::limit(strip_tags($data['konten']), 160);
        $news->body       = $data['konten'];
        $news->event_date = $data['event_date'];
        $news->type       = $data['type'];

        $news->save();

        return redirect()
            ->route('admin.berita.index')
            ->with('success', 'Berita berhasil diperbarui.');
    }

    public function destroy(News $news)
    {
        $news->delete();

        return redirect()
            ->route('admin.berita.index')
            ->with('success', 'Berita berhasil dihapus.');
    }
}
