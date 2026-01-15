<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBankDetailsRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'bank_name' => 'nullable|string|max:255',
            'account_holder_name' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:20',
            'iban' => 'nullable|string|max:34', // IBANs have a maximum of 34 characters
            'swift_code' => 'nullable|string|max:11',
            'id' => [
                'required',
                'integer',
                Rule::exists('bank_details', 'id')
                    ->where('user_id', auth()->id()),
            ],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
