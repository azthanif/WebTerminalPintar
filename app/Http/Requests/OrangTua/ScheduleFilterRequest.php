<?php

namespace App\Http\Requests\OrangTua;

use Illuminate\Foundation\Http\FormRequest;

class ScheduleFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('parent.dashboard.schedules') ?? false;
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', 'in:scheduled,completed,canceled'],
            'start_date' => ['nullable', 'date'],
            'end_date'   => ['nullable', 'date', 'after_or_equal:start_date'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:50'],
        ];
    }
}
