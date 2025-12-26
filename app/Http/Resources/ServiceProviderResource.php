<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\ServiceProvider */
class ServiceProviderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'is_blocked' => $this->is_blocked,
            'is_active' => $this->is_active,
            'email' => $this->email,
            'phone_number' => $this->phone_number,
            'biography' => $this->biography,
            'address' => AddressResource::make($this->address) ?? null,
            'commercial_register' => $this->commercial_register,
            'twitter' => $this->social['twitter'] ?? null,
            'snapchat' => $this->social['snapchat'] ?? null,
            'instagram' => $this->social['instagram'] ?? null,
            'tiktok' => $this->social['tiktok'] ?? null,
            'average_rate' => round($this->reviews->avg('rate'), 1) ?? null,
            'images' => $this->getImagesAttribute(),
            'profile_picture' => $this->getFirstMediaUrl('service_provider_profile_image') ? $this->getMedia('service_provider_profile_image')->last()->getUrl() : asset('assets/default.jpg'),
            'services' => AttachedServiceResource::collection($this->attachedServices) ?? null,
            'provider_type' => $this->providerType?->title,
            'max_appointments_per_day' => $this->max_appointments_per_day,
            'deposit_type' => $this->deposit_type,
            'deposit_amount' => $this->deposit_amount,
            'expected_response_time' => $this->avg_response_time,
            'min_start_time' => $this->getMinStartTime() ?? '00:00:00',
            'max_end_time' => $this->getMaxEndTime() ?? '23:00:00',
            'min_service_price' => $this->getMinServicePrice(),
            'max_service_price' => $this->getMaxServicePrice(),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            'profile_complete_percentage' => $this->profileCompletionPercentage(),
            'remaining_profile_fields' => $this->getRemainingFields(),
            'is_premium' => $this->user->activeSubscription ? true : false,
            'distance_km' => (isset($this->distance) && $this->distance !== PHP_FLOAT_MAX) ? round($this->distance, 2) : null,
            'cancellation_enabled' => $this->cancellation_enabled ?? false,
            'cancellation_hours_before' => $this->cancellation_hours_before,
            'minimum_booking_lead_time_hours' => $this->minimum_booking_lead_time_hours,
            'maximum_booking_lead_time_months' => $this->maximum_booking_lead_time_months,
        ];

        return $data;
    }

    public function getImagesAttribute()
    {
        $images = $this->getMedia('service_provider_images')->map(function ($image) {
            return $image->getUrl();
        });

        if ($images->count() == 1) {
            return $images;
        }

        if ($images->count() > 1) {
            return $images;
        }

        return [asset('assets/default.jpg')];

    }
}
