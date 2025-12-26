<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DeleteUserRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'access_type' => 'required|string',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
