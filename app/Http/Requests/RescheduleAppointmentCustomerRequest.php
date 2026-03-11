<?php

namespace App\Http\Requests;

use App\Rules\ValidSlot;
use Illuminate\Foundation\Http\FormRequest;

class RescheduleAppointmentCustomerRequest extends FormRequest
{

    // protected function prepareForValidation()
    // {
    //     $decodedSlots = [];

    //     if (is_array($this->slots)) {
    //         foreach ($this->slots as $slot) {
    //             $decoded = json_decode($slot, true);

    //             if (json_last_error() === JSON_ERROR_NONE) {
    //                 $decodedSlots[] = $decoded;
    //             }
    //         }
    //     }

    //     $this->merge([
    //         'slots' => $decodedSlots
    //     ]);
    // }
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'appointment_id' => 'required|exists:appointments,id',
             'slots' => 'required|string',
           // 'slots' => 'required|array',
          //  'slots.*' => ['required', 'string', new ValidSlot],
            // 'slots.*.service_id' => 'required|integer',
            // 'slots.*.timeslot' => 'required|string',   
            'date' => 'required',
            'employee_id' => 'nullable',
        ];
    }
}
