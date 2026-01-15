<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChangeCartItemQuantityRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'number_of_beneficiaries' => ['required', 'integer', 'min:1'],
        ];
    }

    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->customer !== null;
    }
}
