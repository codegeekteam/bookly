<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAttachedServiceRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'price' => 'nullable|numeric',
            'has_deposit' => 'nullable|integer|in:0,1',
            'deposit' => 'required_if:has_deposit,1|numeric|min:0|max:100',
            'service_id' => 'nullable|exists:services,id',
            'description' => 'nullable|string',
            'delivery_types' => 'nullable|array',
            'delivery_types.*' => 'nullable|exists:delivery_types,id',
            'ops_hours' => 'required|array',
            'ops_hours.*.day' => 'required|in:SAT,SUN,MON,TUE,WED,THU,FRI',
            'ops_hours.*.start_time' => 'required|date_format:H:i',
            'ops_hours.*.end_time' => 'required|date_format:H:i|after:from',
            'ops_hours.*.duration_in_minutes' => 'required|integer|min:1|max:1440',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
