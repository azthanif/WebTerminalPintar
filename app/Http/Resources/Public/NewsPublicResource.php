<?php

namespace App\Http\Resources\Public;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
            'gambar_url'        => $this->cover_image_path,
        ];
    }
}
