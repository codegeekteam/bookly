<?php

namespace App\Http\Requests;

use App\Models\Appointment;
use Illuminate\Foundation\Http\FormRequest;

class CancelAppointmentByProviderRequest extends FormRequest
{

    public function rules(): array
    {
        return [
            'appointment_id' => 'required|exists:appointments,id',
        ];
    }

    public function authorize(): bool
    {
        return auth()->user()?->serviceProvider?->id === Appointment::find($this->appointment_id)->service_provider_id;
    }

}
