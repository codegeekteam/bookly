<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VerifyOTPRequest extends FormRequest
{
    public function rules(): array
    {
        return [           
            'phone_number'   => 'required|string',
            'otp'  => 'required|numeric|digits:6',
            'access_type' => 'nullable|string|in:customer,employee,provider',
            'firebase_token' => 'nullable|string',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
