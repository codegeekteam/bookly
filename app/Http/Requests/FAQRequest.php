<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FAQRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'question' => ['required'],
            'answer' => ['required'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
