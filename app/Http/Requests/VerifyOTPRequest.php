<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VerifyOTPRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'otp' => 'required|numeric|digits:6',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
