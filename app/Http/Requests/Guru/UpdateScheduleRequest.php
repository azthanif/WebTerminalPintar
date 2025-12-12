<?php

namespace App\Http\Requests\Guru;

class UpdateScheduleRequest extends StoreScheduleRequest
{
    public function rules(): array
    {
        $rules = parent::rules();
        $rules['student_ids'][0] = 'sometimes';
        $rules['timezone'][0] = 'sometimes';

        return $rules;
    }
}
