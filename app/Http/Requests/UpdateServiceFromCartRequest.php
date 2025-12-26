<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateServiceFromCartRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'number_of_beneficiaries' => ['integer', 'min:1', 'max:6'],
            'time_slot' => ['required', 'string'], // Allow an array of time slots
            'delivery_type_id' => ['required', 'exists:delivery_types,id'],
            'address_id' => ['nullable', 'exists:addresses,id'],
        ];
    }

    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->customer !== null;
    }
}
