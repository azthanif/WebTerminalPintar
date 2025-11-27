<?php

namespace App\Http\Requests\OrangTua;

use Illuminate\Foundation\Http\FormRequest;

class TeacherNoteFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('parent.dashboard.notes') ?? false;
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:150'],
            'category' => ['nullable', 'in:behavior,academic,communication,general'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:50'],
        ];
    }
}
