<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WalletCashoutRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'bank_name' => ['required', 'string'],
            'account_number' => ['required', 'string'],
            'account_name' => ['required', 'string'],
            'iban' => ['required', 'string'],
            'amount' => ['required_if:full_amount,0', 'numeric'],
            'full_amount' => ['required', 'boolean'],
        ];
    }

    public function prepareForValidation(): void
    {
        $this->mergeIfMissing([
            'user_id' => auth()->id(),
        ]);
    }
}
