<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DeferredPayoutResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $firstService = $this->appointment->appointmentServices->first();
        $date = $firstService?->date;

        if ($date instanceof \Carbon\Carbon) {
            $appointmentDate = $date->format('Y-m-d');
        } elseif (is_string($date)) {
            $appointmentDate = $date;
        } else {
            $appointmentDate = null;
        }

        return [
            'id' => $this->id,
            'amount' => (float) $this->amount,
            'appointment_id' => $this->appointment_id,
            'appointment_date' => $appointmentDate,
            'payment_type' => $this->payment_type,
            'payment_method' => $this->paymentMethod?->name,
            'completed_at' => $this->completed_at?->toDateTimeString(),
            'available_at' => $this->available_at?->toDateTimeString(),
            'status' => $this->status,
        ];
    }
}
