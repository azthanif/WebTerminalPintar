<?php

namespace App\Http\Requests\Admin\News;

use Illuminate\Foundation\Http\FormRequest;

class UpdateNewsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role?->name === 'admin';
    }

    public function rules(): array
    {
        return [
            'judul'        => ['required', 'string', 'max:255'],
            'konten'       => ['required', 'string'],
            'event_date'   => ['nullable', 'date'],
            'type'         => ['required', 'in:news,activity,gallery'],
        ];
    }
}
