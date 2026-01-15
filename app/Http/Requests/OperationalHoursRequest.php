<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OperationalHoursRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'attached_service_id' => ['required'],
            'saturday' => ['nullable'],
            'sunday' => ['nullable'],
            'monday' => ['nullable'],
            'tuesday' => ['nullable'],
            'wednesday' => ['nullable'],
            'thursday' => ['nullable'],
            'friday' => ['nullable'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
