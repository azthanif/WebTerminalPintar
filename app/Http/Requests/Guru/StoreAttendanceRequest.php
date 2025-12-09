<?php

namespace App\Http\Requests\Guru;

use Illuminate\Foundation\Http\FormRequest;

class StoreAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('guru') ?? false;
    }

    public function rules(): array
    {
        return [
            'schedule_id' => ['required', 'exists:schedules,id'],
            'student_id' => ['required', 'exists:students,id'],
            'status' => ['required', 'in:Hadir,Sakit,Izin,Alpha'],
            'attendance_date' => ['nullable', 'date'],
            'recorded_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'session_topic' => ['nullable', 'string', 'max:150'],
            'session_time' => ['nullable', 'string', 'max:100'],
            'requires_follow_up' => ['nullable', 'boolean'],
            'input_channel' => ['nullable', 'string', 'max:30'],
        ];
    }
}
