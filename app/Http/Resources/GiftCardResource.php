<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\GiftCard */
class GiftCardResource extends JsonResource
{
    public static $wrap = null;

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'theme' => $this->relationLoaded('theme') ? new GiftCardThemeResource($this->theme) : null,
            'user' => $this->getUser(),
            'amount' => $this->amount,
            'recipient_name' => $this->recipient_name,
            'recipient_phone_number' => $this->recipient_phone_number,
            'is_used' => $this->is_used,
            'used_by' => $this->usedBy ? $this->usedBy->first_name.' '.$this->usedBy->last_name : null,
            'appointment_id' => $this->appointment_id,
            'payment_status' => $this->payment_status,
            'created_at' => $this->created_at,
            'used_at' => $this->used_at,
        ];
    }

    public function getUser()
    {
        if($this->user){
            if($this->user->customer)
            {
                return $this->user->customer->first_name.' '.$this->user->customer->last_name;
            }
            if($this->user->serviceProvider)
            {
                return $this->user->serviceProvider->name;
            }
            return null;
        }
        return null;
    }
}
