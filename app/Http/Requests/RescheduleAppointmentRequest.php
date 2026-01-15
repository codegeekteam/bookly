<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RescheduleAppointmentRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'appointment_id' => 'required|exists:appointments,id',
            'service_id' => 'required',
            'timeslot' => 'required',
            'date' => 'required',
            'employee_id' => 'nullable',
        ];
    }
}
