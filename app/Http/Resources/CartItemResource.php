<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class CartItemResource
 * @mixin \App\Models\CartItem
 */
class CartItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'attached_service_id' => $this->attached_service_id,
            'service_id' => $this->service_id,
            'number_of_beneficiaries' => $this->quantity,
            'title' => $this->title,
            'provider_id' => $this->provider_id,
            'provider_name' => $this->provider_name,
            'provider_image' => $this->provider_image,
            'provider_type' => $this->provider_type,
            'picked_date' => $this->picked_date,
            'time_slot' => $this->time_slot,
            'delivery_type_id' => $this->delivery_type_id,
            'delivery_type' => $this->delivery_type_title,
            'rating' => $this->rating,
            'is_favorite' => $this->is_favorite,
            'price' => $this->price,
            'total' => $this->total,
            'has_deposit' => $this->has_deposit,
            'deposit' => $this->deposit,
            'deposit_amount' => $this->deposit_amount,
            'amount_due' => $this->amount_due,
            'address' => $this->address?new AddressResource($this->address):null,
            'employee' => $this->employee ? [
                'id' => $this->employee->id,
                'name' => $this->employee->name,
                'profile_picture' => $this->employee->getFirstMediaUrl('profile_pictures') ?: asset('assets/default.jpg'),
            ] : null,
        ];
    }
}
