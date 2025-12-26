<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\CustomerCampaign */
class CustomerCampaignResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'hot_services' => ServiceResource::collection($this->services),
            'popular_providers' => ServiceProviderResource::collection($this->providers),
            'banners' => $this->getFirstMediaUrl('banners') ? $this->getMedia('banners')->map(function ($banner) {
                return $banner->getUrl();
            }) : [asset('assets/default.jpg')],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
