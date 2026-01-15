<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @property mixed $services
 * @property mixed $promo_code
 * @property mixed $comment
 * @property mixed $payment_method_id
 */
class BookAppointmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'services' => 'required|array',
            'services.*.service_id' => 'required|exists:services,id',
            'services.*.provider_id' => 'required|exists:service_providers,id',
            'services.*.number_of_beneficiaries' => 'required|integer|min:1|max:6',
            'services.*.delivery_type_id' => 'required|exists:delivery_types,id',
            'services.*.address_id' => 'nullable|exists:addresses,id',
            'services.*.employee_id' => 'nullable|exists:employees,id',
            'services.*.time_slot' => 'required',
            'services.*.date' => 'required|date',
            'promo_code' => 'nullable',
            'loyalty_discount_customer_id' => 'nullable|integer',
            'comment' => 'nullable|max:255',
            'payment_method_id' => 'required|exists:payment_methods,id',
        ];
    }
}
