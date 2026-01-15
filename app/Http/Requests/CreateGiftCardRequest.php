<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateGiftCardRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'gift_card_theme_id' => ['required', 'integer','exists:gift_card_themes,id'],
            'recipient_name' => ['required'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'recipient_phone_number' => ['required','numeric','digits:9'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
