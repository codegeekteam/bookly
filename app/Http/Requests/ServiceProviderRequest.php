<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ServiceProviderRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => ['required'],
            'is_blocked' => ['required'],
            'email' => ['required', 'email', 'max:254'],
            'phone_number' => ['required'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
