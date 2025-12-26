<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FavouriteRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'service_id' => ['required', 'integer', 'exists:attached_services,id'],
        ];
    }

    public function authorize(): bool
    {
        return auth()->user()->customer !== null;
    }
}
