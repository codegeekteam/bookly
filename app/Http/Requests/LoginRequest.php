<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'phone_number' => 'required|numeric|digits:9',
            // TODO : add access type validation
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
