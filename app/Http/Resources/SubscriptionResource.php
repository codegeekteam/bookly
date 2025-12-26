<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Subscription */
class SubscriptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'plan_id' => $this->plan_id,
            'user_id' => $this->user_id,
            'amount_paid' => $this->amount_paid,
            'expires_at' => $this->expires_at,
            'start_date' => $this->start_date,
            'plan' => new PlanResource($this->plan),
            'created_at' => $this->created_at,
            'payment_status' => $this->payment_status,
        ];
    }
}
