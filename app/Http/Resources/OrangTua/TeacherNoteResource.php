<?php

namespace App\Http\Resources\OrangTua;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeacherNoteResource extends JsonResource
{
    public static $wrap = null;
    /** @return array<string,mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'note' => $this->note,
            'category' => $this->category,
            'recorded_at' => optional($this->recorded_at)->toIsoString(),
            'recorded_at_readable' => optional($this->recorded_at)->translatedFormat('l, d M Y H:i'),
            'teacher' => [
                'id' => $this->teacher?->id,
                'name' => $this->teacher?->name,
            ],
        ];
    }
}
