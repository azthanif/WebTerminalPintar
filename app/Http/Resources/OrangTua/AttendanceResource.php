<?php

namespace App\Http\Resources\OrangTua;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceResource extends JsonResource
{
    public static $wrap = null;
    
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'attendance_date' => optional($this->attendance_date)->toIsoString(), 
            'start_time_raw' => $this->schedule ? optional($this->schedule->start_time)->toIsoString() : null,
            'end_time_raw' => $this->schedule ? optional($this->schedule->end_time)->toIsoString() : null,
            
            'session_topic' => $this->session_topic,
            'session_time' => $this->session_time, 
            'notes' => $this->notes,
        ];
    }
}