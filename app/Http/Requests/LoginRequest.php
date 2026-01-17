<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'phone_number' => 'required|numeric|digits:9',
            'access_type' => 'required|string|in:customer,employee,provider',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
