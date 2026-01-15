<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VerifyLoyaltyDiscountRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'id' => ['required','integer','exists:loyalty_discount_customers,id'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
