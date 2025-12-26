<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddressRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'address_name' => ['required'],
            'latitude' => ['required', 'numeric'],
            'longitude' => ['required', 'numeric'],
            'address_details' => ['nullable'],
            'is_default' => 'nullable|in:0,1',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
