<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PayoutHistoryResource extends JsonResource
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
            'total_amount' => (float) $this->total_amount,
            'due_date' => $this->due_date?->format('Y-m-d'),
            'status' => $this->status,
            'transferred_at' => $this->transferred_at?->toDateTimeString(),
            'appointment_count' => $this->deferredPayouts->count(),
            'appointments' => DeferredPayoutResource::collection($this->whenLoaded('deferredPayouts')),
        ];
    }
}
