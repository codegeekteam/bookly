<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Review */
class ReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'rate' => $this->rate,
            'comment' => $this->comment,
            'customer_name' => $this->customer->first_name.' '.$this->customer->last_name,
            'customer_profile_picture' => $this->customer->getFirstMediaUrl('profile_picture') ? $this->customer->getMedia('profile_picture')->last()->getUrl() : asset('assets/default.jpg'),
            'service_name' => $this->service->title,
            'service_image' => $this->service->categories->first()?->getFirstMediaUrl('category_images') ? $this->service->categories->first()?->getMedia('category_images')->last()->getUrl() : asset('assets/default.jpg'),
            'provider_name' => $this->serviceProvider->name,
            'provider_type' => $this->serviceProvider->providerType->title,
            'provider_profile_picture' => $this->serviceProvider->getFirstMediaUrl('service_provider_profile_image') ? $this->serviceProvider->getMedia('service_provider_profile_image')->last()->getUrl() : asset('assets/default.jpg'),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
