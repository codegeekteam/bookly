<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function rules(): array
    {
        $userId = auth()->id(); // Get authenticated user ID
        $customer = auth()->user()->customer;
        $serviceProvider = auth()->user()->serviceProvider;
        $customerId=$customer?$customer->id:null;
        $serviceProviderId=$serviceProvider?$serviceProvider->id:null;
        return [
            'first_name' => ['nullable', 'string'],
            'last_name' => ['nullable', 'string'],
            'email' => 'nullable|email|unique:users,email,'.auth()->id(),
            'phone_number' => [
                'nullable',
                'numeric',
                'digits:9',
                Rule::when($customer, Rule::unique('customers', 'phone_number')->ignore($customerId, 'id')),
                Rule::when($serviceProvider, Rule::unique('service_providers', 'phone_number')->ignore($serviceProviderId, 'id')),
            ],
            'provider_type_id' => ['nullable', 'numeric', 'exists:provider_types,id'],
            'provider_id' => ['nullable', 'numeric', 'exists:providers,id'],
            'profile_picture' => ['nullable', 'image', 'mimes:png,jpg,jpeg'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
