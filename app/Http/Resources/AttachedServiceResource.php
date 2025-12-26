<?php

namespace App\Http\Resources;

use App\Models\AttachedService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin AttachedService */
class AttachedServiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'service' => $this->service ?->getTranslation('title', $request->header('lang') ?? 'en'),
            'service_id' => $this->service_id,
            'service_description' => $this->description ?? $this->service ?->description,
            'currency' => 'SAR',
            'service_image' => $this->service ?->categories->first() ?->getFirstMediaUrl('category_images') ? $this->service ?->categories->first() ?->getMedia('category_images') ?->last() ?->getUrl() : asset('assets/default.jpg'),
            'service_provider' => $this->serviceProvider ?->name ?? '-',
            'service_provider_type' => $this->serviceProvider ?->providerType ?->title ?? '-',
            'service_provider_image' => $this->serviceProvider ?->getFirstMediaUrl('service_provider_profile_image') ? $this->serviceProvider ?->getMedia('service_provider_profile_image')->last()->getUrl() : asset('assets/default.jpg'),
            'rating' => $this->service ? $this->service->reviews ?->avg('rate') : null,
            'is_favourite' => $this->favourites ?->contains('customer_id', auth()->guard('api')->user() ?->customer ?->id),
            'price' => $this->price ?? 0.0,
            'has_deposit' => $this->has_deposit ?? 0,
            'deposit' => $this->deposit ?? 0.0,
            'deposit_amount' => $this->deposit_amount ?? 0.0,
            'min_price' => floatval($this->where('service_id', $this->service_id)->min('price')),
            'max_price' => floatval($this->where('service_id', $this->service_id)->max('price')),
            'service_provider_id' => $this->service_provider_id,
            'average_rate' => $this->service ? $this->service->reviews ?->avg('rate') : null,
            'my_place' => $this->deliveryTypes ?->contains('id', 1),
            'customer_place' => $this->deliveryTypes ?->contains('id', 2),
            'operational_hours' => $this->operationalHours() ?->map(function ($ops_hour) {
                return [
                    'day' => $this->getDayOfTheWeekShortName($ops_hour['day_of_week']),
                    'start_time' => Carbon::parse($ops_hour['start_time'])->format('H:i'),
                    'end_time' => Carbon::parse($ops_hour['end_time'])->format('H:i'),
                    'duration_in_minutes' => $ops_hour['duration_in_minutes'],
                ];
            }),
            'duration' => $this->operationalHours()->first() ? $this->operationalHours()->first()->duration_in_minutes . '' : '0',
            'operational_off_hours' => $this->operationalOffHours() ?->map(function ($ops_hour) {
                return [
                    'day' => $this->getDayOfTheWeekShortName($ops_hour['day_of_week']),
                    'start_time' => Carbon::parse($ops_hour['start_time'])->format('H:i'),
                    'end_time' => Carbon::parse($ops_hour['end_time'])->format('H:i'),
                ];
            }),
            'nearest_available_slot' => $this->nearest_slot_info['label'] ?? null,

        ];
    }

    public function getDayOfTheWeekShortName(string $day)
    {
        return match ($day) {
            'Saturday' => 'SAT',
            'Sunday' => 'SUN',
            'Monday' => 'MON',
            'Tuesday' => 'TUE',
            'Wednesday' => 'WED',
            'Thursday' => 'THU',
            'Friday' => 'FRI',
            default => $day,
        };
    }
}
