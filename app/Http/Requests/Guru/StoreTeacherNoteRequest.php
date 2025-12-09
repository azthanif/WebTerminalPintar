<?php

namespace App\Http\Requests\Guru;

use Illuminate\Foundation\Http\FormRequest;

class StoreTeacherNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('guru') ?? false;
    }

    public function rules(): array
    {
        return [
            'student_id' => ['required', 'exists:students,id'],
            'schedule_id' => ['nullable', 'exists:schedules,id'],
            'title' => ['required', 'string', 'max:150'],
            'note' => ['required', 'string'],
            'category' => ['required', 'in:behavior,academic,communication,general'],
            'visibility' => ['nullable', 'in:parent,admin_only'],
            'tag_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'sentiment' => ['nullable', 'string', 'max:20'],
            'is_flagged' => ['nullable', 'boolean'],
            'follow_up_actions' => ['nullable', 'string'],
            'attachments' => ['nullable', 'array'],
        ];
    }
}
