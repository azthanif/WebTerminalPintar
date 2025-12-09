<?php

namespace App\Http\Requests\Guru;

use Illuminate\Foundation\Http\FormRequest;

class StoreMaterialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('guru') ?? false;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'status' => ['nullable', 'string', 'max:30'],
            'file' => ['nullable', 'file', 'max:5120'],
            'download_url' => ['nullable', 'string', 'max:255'],
            'visibility' => ['nullable', 'string', 'max:20'],
            'labels' => ['nullable', 'array'],
        ];
    }
}
