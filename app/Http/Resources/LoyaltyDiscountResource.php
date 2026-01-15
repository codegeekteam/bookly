<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\LoyaltyDiscount */
class LoyaltyDiscountResource extends JsonResource
{
    public function toArray(Request $request): array
    {

        return [
            'id' => $this->id,
            'discount_amount' => $this->discount_amount . '',
            'points' => $this->points,
            'discount_type' => $this->discountType ? $this->discountType->title : '',
            'maximum_discount' => $this->maximum_discount . '',
            'minimum_amount' => $this->minimum_amount . '',
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'is_redeemed' => $this->is_redeemed ?? false,
        ];
    }
}
