<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReviewRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'rate' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable'],
            'appointment_id' => ['required', 'exists:appointments,id'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
