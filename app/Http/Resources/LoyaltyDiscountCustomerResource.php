<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\LoyaltyDiscountCustomer */
class LoyaltyDiscountCustomerResource extends JsonResource
{
    public function toArray(Request $request): array
    {

        return [
            'id' => $this->id,
            'loyalty_discount' => $this->relationLoaded('loyaltyDiscount') ? new LoyaltyDiscountResource($this->loyaltyDiscount) : null,
            'discount_amount' => $this->discount_amount . '',
            'points' => $this->points,
            'discount_type' => $this->discountType ? $this->discountType->title : '',
            'maximum_discount' => $this->maximum_discount . '',
            'minimum_amount' => $this->minimum_amount . '',
            'is_used' => $this->is_used ?? false,
        ];
    }
}
