<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubscriptionRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'plan_id' => ['required','exists:plans,id'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
