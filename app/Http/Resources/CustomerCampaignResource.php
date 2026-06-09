<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\CustomerCampaign */
class CustomerCampaignResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $media = $this->getMedia('banners');
        return [
           // 'hot_services' => ServiceResource::collection($this->services),
           'hot_Services' => HotServiceResource::collection($this->services),
          //  'popular_providers' => ServiceProviderResource::collection($this->providers),
          'popular_providers' => PopularServiceProviderResource::collection($this->providers),
            // 'banners' => $this->getFirstMediaUrl('banners') ? $this->getMedia('banners')->map(function ($banner) {
            //     return $banner->getUrl();
            // }) : [asset('assets/default.jpg')],
            'banners' => $media->isNotEmpty()
                ? $media->map(fn ($banner) => $banner->getUrl())
                : [asset('assets/default.jpg')],
           // 'created_at' => $this->created_at,
           // 'updated_at' => $this->updated_at,
        ];
    }
}
