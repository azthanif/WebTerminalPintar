<?php

namespace App\Http\Resources\OrangTua;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentResource extends JsonResource
{
    public static $wrap = null;
    /** @return array<string,mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'student_id' => $this->student_id,
            'education_level' => $this->education_level,
            'status' => $this->status,
            'school_name' => $this->school_name,
            'date_of_birth' => optional($this->date_of_birth)->toDateString(),
        ];
    }
}
