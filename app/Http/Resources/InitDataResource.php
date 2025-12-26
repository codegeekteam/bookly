<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Category */
class InitDataResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->getTranslation('title', $request->header('lang') ?? 'en'),
            'icon' => $this->getMedia('category_images')->last()?->getUrl() ?? asset('assets/default.jpg'),
            'min_price' => $this->services->flatMap(function ($service) {
                return $service->attachedServices->pluck('price');
            })->min(),
            'max_price' => $this->services->flatMap(function ($service) {
                return $service->attachedServices->pluck('price');
            })->max(),
            'currency' => 'SAR',
        ];
    }
}
