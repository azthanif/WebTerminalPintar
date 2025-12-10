<?php

namespace App\Http\Resources\Public;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class NewsPublicResource extends JsonResource
{
    public static $wrap = null;

    /**
     * @param  Request  $request
     */
    public function toArray($request): array
    {
        $primaryImage = $this->resolveCoverImageUrl($this->cover_image_path);
        $secondaryImage = $this->resolveCoverImageUrl($this->second_image_path);

        return [
            'id'                => $this->id,
            'judul'             => $this->title,
            'sub_judul'         => $this->subtitle,
            'slug'              => $this->slug,
            'tanggal_publikasi' => optional($this->event_date ?? $this->created_at)->toDateString(),
            'deskripsi_singkat' => $this->excerpt,
            'deskripsi'         => Str::limit(strip_tags($this->body), 200),
            'konten'            => $this->body,
            'gambar_url'        => $primaryImage,
            'gallery'           => array_values(array_filter([$primaryImage, $secondaryImage])),
            'waktu_lalu'        => $this->created_at?->diffForHumans(),
        ];
    }

    protected function resolveCoverImageUrl(?string $path = null): ?string
    {
        if (! $path) {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        return Storage::url($path);
    }
}
