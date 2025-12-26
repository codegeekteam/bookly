<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RedeemLoyaltyPointDiscountRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'id' => ['required','integer','exists:loyalty_discounts,id'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
