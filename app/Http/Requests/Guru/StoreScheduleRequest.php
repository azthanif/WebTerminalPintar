<?php

namespace App\Http\Requests\Guru;

use Illuminate\Foundation\Http\FormRequest;

class StoreScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('guru') ?? false;
    }

    public function rules(): array
    {
        return [
            'student_ids' => ['required', 'array', 'min:1'],
            'student_ids.*' => ['integer', 'exists:students,id'],
            'subject' => ['required', 'string', 'max:150'],
            'topic' => ['required', 'string', 'max:150'],
            'learning_focus' => ['nullable', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'start_time' => ['required', 'date'],
            'end_time' => ['nullable', 'date', 'after_or_equal:start_time'],
            'location' => ['nullable', 'string', 'max:150'],
            'meeting_url' => ['nullable', 'url'],
            'max_participants' => ['nullable', 'integer', 'min:1', 'max:100'],
            'status_badge' => ['nullable', 'in:Akan Datang,Berlangsung,Selesai,Dibatalkan'],
            'attachments_meta' => ['nullable', 'array'],
            'timezone' => ['nullable', 'timezone'],
        ];
    }
}
