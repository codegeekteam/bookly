<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AppointmentRescheduleRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'appointment_id' => 'required|exists:appointments,id',
            'service_id' => 'required|exists:services,id',
            'customer_response' => 'required|string',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
