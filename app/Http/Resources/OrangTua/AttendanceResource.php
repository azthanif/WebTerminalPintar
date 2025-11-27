<?php

namespace App\Http\Resources\OrangTua;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceResource extends JsonResource
{
    public static $wrap = null;
    /** @return array<string,mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'attendance_date' => optional($this->attendance_date)->toDateString(),
            'attendance_date_readable' => optional($this->attendance_date)->translatedFormat('l, d M Y'),
            'session_topic' => $this->session_topic,
            'session_time' => $this->session_time,
            'notes' => $this->notes,
        ];
    }
}
