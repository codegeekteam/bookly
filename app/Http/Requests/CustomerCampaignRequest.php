<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CustomerCampaignRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => ['required'],
            'is_active' => ['required'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
