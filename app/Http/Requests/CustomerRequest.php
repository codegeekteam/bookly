<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CustomerRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'first_name' => ['required'],
            'last_name' => ['required'],
            'email' => ['required', 'email', 'max:254', 'unique:customers'],
            'date_of_birth' => ['required'],
            'phone_number' => ['required'],
            'is_blocked' => ['required'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
