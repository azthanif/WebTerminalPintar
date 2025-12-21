<?php

namespace App\Http\Requests\Admin\Student;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('admin') ?? false;
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
            'create_parent_account' => ['nullable', 'boolean'],
            'new_parent_name'       => ['nullable', 'required_if:create_parent_account,true', 'string', 'max:150'],
            'new_parent_email'      => ['nullable', 'required_if:create_parent_account,true', 'email', 'max:150', 'unique:users,email'],
            'new_parent_phone'      => ['nullable', 'string', 'max:30'],
            'status'          => ['required', 'in:active,inactive'],
            'date_of_birth'   => ['nullable', 'date', 'before:today'],
            'school_name'     => ['nullable', 'string', 'max:150'],
            'address'         => ['nullable', 'string'],
        ];
    }
}
