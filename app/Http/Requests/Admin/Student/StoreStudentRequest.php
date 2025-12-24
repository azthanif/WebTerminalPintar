<?php

namespace App\Http\Requests\Admin\Student;

use App\Models\Student;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('admin') ?? false;
    }

    public function rules(): array
    {
        return [
            'student_id'      => [
                'required',
                'string',
                'max:50',
                Rule::unique('students', 'student_id')->whereNull('deleted_at'),
            ],
            'name'            => ['required', 'string', 'max:150'],
            'education_level' => ['nullable', 'string', 'max:50'],
            'parent_id'       => ['nullable', 'exists:users,id'],
            'create_parent_account' => ['nullable', 'boolean'],
            'new_parent_name'       => ['nullable', 'required_if:create_parent_account,true', 'string', 'max:150'],
            'new_parent_email'      => ['nullable', 'required_if:create_parent_account,true', 'email', 'max:150', 'unique:users,email'],
            'new_parent_phone'      => ['nullable', 'string', 'max:30'],
            'new_parent_password'   => ['nullable', 'string', 'min:8'],
            'status'          => ['required', 'in:active,inactive'],
            'date_of_birth'   => ['required', 'date', 'before:today'],
            'school_name'     => ['nullable', 'string', 'max:150'],
            'address'         => ['nullable', 'string'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if (! $this->filled('student_id')) {
            $this->merge([
                'student_id' => Student::generateStudentId(),
            ]);
        }
    }
}
