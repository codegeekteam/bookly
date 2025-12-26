<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ServiceProviderSettingsRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'max_appointments_per_day' => ['nullable', 'integer', 'min:1', 'max:2000'],
            'deposit_type' => ['nullable', 'string', 'in:fixed,percentage'],
            'deposit_amount' => ['nullable', 'integer', $this->input('deposit_type') == 'percentage' ? 'max:100' : 'max:100000'],
            'cancellation_enabled' => ['nullable', 'boolean'],
            'cancellation_hours_before' => ['nullable', 'integer', 'min:1', 'max:168'],
            'minimum_booking_lead_time_hours' => ['nullable', 'integer', 'min:0', 'max:4320'],
            'maximum_booking_lead_time_months' => ['nullable', 'integer', 'min:1', 'max:12'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
