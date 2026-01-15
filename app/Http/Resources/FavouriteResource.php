<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Favourite */
class FavouriteResource extends JsonResource
{
    public static $wrap = null;

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'service' => new AttachedServiceResource($this->service),
            'provider_name' => $this->service?->serviceProvider?->name ?? '-',
            'provider_type' => $this->service?->serviceProvider?->providerType?->title,
            'provider_profile_picture' => $this->service?->serviceProvider?->getFirstMediaUrl('profile_pictures') ? $this->service?->serviceProvider?->getMedia('profile_pictures')->last()->getUrl() : asset('assets/default.jpg'),
        ];
    }
}
