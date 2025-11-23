<?php

namespace App\Http\Resources\Public;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class NewsPublicResource extends JsonResource
{
    /**
     * @param  Request  $request
     */
    public function toArray($request): array
    {
        return [
            'id'                => $this->id,
            'judul'             => $this->title,
            'slug'              => $this->slug,
            'tanggal_publikasi' => optional($this->event_date ?? $this->created_at)->toDateString(),
            'deskripsi_singkat' => $this->excerpt,
            'konten'            => $this->body,
            'gambar_url'        => $this->resolveCoverImageUrl(),
            'waktu_lalu'        => $this->created_at?->diffForHumans(),
        ];
    }

    protected function resolveCoverImageUrl(): ?string
    {
        if (! $this->cover_image_path) {
            return null;
        }

        if (Str::startsWith($this->cover_image_path, ['http://', 'https://'])) {
            return $this->cover_image_path;
        }

        return Storage::url($this->cover_image_path);
    }
}
