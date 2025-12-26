<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ChangeRemainingPaymentMethodRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'payment_method_id' => ['required', 'integer', 'exists:payment_methods,id'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $appointment = $this->route('appointment');

            // Check if appointment belongs to the authenticated customer
            if ($appointment && $appointment->customer_id !== auth()->user()->customer->id) {
                $validator->errors()->add('appointment', 'You are not authorized to modify this appointment.');
                return;
            }

            // Check if remaining amount exists
            if (!$appointment->remaining_amount || $appointment->remaining_amount <= 0) {
                $validator->errors()->add('remaining_amount', 'This appointment does not have a remaining payment.');
                return;
            }

            // Check if remaining payment is still pending
            if ($appointment->remaining_payment_status !== 'pending') {
                $validator->errors()->add('remaining_payment_status', 'The remaining payment has already been processed and cannot be changed.');
                return;
            }

            // Check if payment method is active
            $paymentMethod = \App\Models\PaymentMethod::find($this->payment_method_id);
            if ($paymentMethod && !$paymentMethod->is_active) {
                $validator->errors()->add('payment_method_id', 'The selected payment method is not currently available.');
            }
        });
    }
}