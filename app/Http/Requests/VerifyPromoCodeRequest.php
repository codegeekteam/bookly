<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VerifyPromoCodeRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            //            'service_provider_id' => 'required|exists:service_providers,id',
            //            'services_ids' => 'required|array',
            //            'services_ids.*' => 'required|exists:services,id',
            'promo_code' => 'required|exists:promo_codes,code',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
