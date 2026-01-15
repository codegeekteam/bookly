<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GetServiceProviderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'rating' => ['nullable', 'numeric', 'min:1', 'max:5'],
            'keyword' => ['nullable', 'string'],
            'sort_direction' => ['nullable', 'string', 'in:asc,desc'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ];
    }
}
