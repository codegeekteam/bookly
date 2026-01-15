<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddServiceToCartRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'attached_service_id' => ['required', 'exists:attached_services,id'],
            'number_of_beneficiaries' => ['integer', 'min:1', 'max:6'],
            'picked_date' => ['required', 'date', 'date_format:Y-m-d','after_or_equal:today'],
            'time_slot' => ['required', 'array'], // Allow an array of time slots
            'time_slot.*' => ['string', 'distinct'], // Validate each time slot
            'delivery_type_id' => ['required', 'exists:delivery_types,id'],
            'address_id' => ['nullable', 'exists:addresses,id'],
            'employee_id' => ['nullable', 'exists:employees,id'],
        ];
    }

    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->customer !== null;
    }
}
