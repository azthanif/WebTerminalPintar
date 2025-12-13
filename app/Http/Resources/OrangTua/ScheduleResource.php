<?php

namespace App\Http\Resources\OrangTua;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScheduleResource extends JsonResource
{
    public static $wrap = null;
    /** @return array<string,mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'subject' => $this->subject,
            'topic' => $this->topic,
            'description' => $this->description,
            'start_time' => optional($this->start_time)->toIsoString(),
            'end_time' => optional($this->end_time)->toIsoString(),
            'start_time_readable' => optional($this->start_time)
                ->timezone('Asia/Jakarta') 
                ->translatedFormat('l, d M Y H:i'),
            'location' => $this->location,
            'status' => $this->status,
            'meeting_url' => $this->meeting_url,
            'teacher' => [
                'id' => $this->teacher?->id,
                'name' => $this->teacher?->name,
            ],
            'materials' => $this->materials->map(fn ($material) => [
                'id' => $material->id,
                'title' => $material->title,
                'description' => $material->description,
                'download_url' => $material->download_url,
                'file_type' => $material->file_type,
            ]),
        ];
    }
}
