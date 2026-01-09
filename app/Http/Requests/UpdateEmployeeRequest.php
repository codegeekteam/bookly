<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEmployeeRequest extends FormRequest
{
    protected function prepareForValidation()
    {
        if ($this->has('service_ids')) {
            if (is_string($this->service_ids)) {
                $ids = array_filter(array_map('trim', explode(',', $this->service_ids)));
                $this->merge([
                    'service_ids' => array_map('intval', $ids)
                ]);
            }
        }
    }

    public function rules(): array
    {
        return [
            'name' => ['nullable'],
            'email' => ['nullable', 'email', 'max:254', 'unique:employees,email,'.$this->route('employee_id')],
            'phone_number' => ['nullable'],
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg', //|max:2048',
            'service_ids' => ['nullable'],
            'service_ids.*' => ['exists:services,id'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
