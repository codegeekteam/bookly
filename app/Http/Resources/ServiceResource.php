<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Service */
class ServiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->getTranslation('title', $request->header('lang') ?? 'en'),
            'is_active' => $this->is_active,
            'description' => $this->description,
            'average_price' => $this->attachedServices->avg('price'),
            'currency' => 'SAR',
            'min_price' => floatval($this->attachedServices->min('price')),
            'max_price' => floatval($this->attachedServices->max('price')),
            'image' => $this->categories->first()?->getFirstMediaUrl('category_images') ? $this->categories->first()?->getMedia('category_images')?->last()?->getUrl() : asset('assets/default.jpg'), // TODO: add ability to add service photo in the dashboard and change the collection accordingly
            'service_providers' => ServiceProviderResource::collection($this->whenLoaded('providers')) ?? null,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
