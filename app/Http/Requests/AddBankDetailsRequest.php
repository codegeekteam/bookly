<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddBankDetailsRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'bank_name' => 'required|string|max:255',
            'account_holder_name' => 'required|string|max:255',
            'account_number' => 'required|string|max:20',
            'iban' => 'required|string|max:34', // IBANs have a maximum of 34 characters
            'swift_code' => 'required|string|max:11',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
