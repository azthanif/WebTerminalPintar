<?php

namespace App\Http\Requests\Admin\Student;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role?->name === 'admin';
    }

    public function rules(): array
    {
        $id = $this->route('student')->id;

        return [
            'student_id'      => [
                'required',
                'string',
                'max:50',
                Rule::unique('students', 'student_id')->ignore($id),
            ],
            'name'            => ['required', 'string', 'max:150'],
            'education_level' => ['nullable', 'string', 'max:50'],
            'parent_id'       => ['nullable', 'exists:users,id'],
            'status'          => ['required', 'in:active,inactive'],
            'date_of_birth'   => ['nullable', 'date'],
            'school_name'     => ['nullable', 'string', 'max:150'],
            'address'         => ['nullable', 'string'],
        ];
    }
}
