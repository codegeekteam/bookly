<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Employee */
class EmployeeResource extends JsonResource
{
    public static $wrap = null;

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone_number' => $this->phone_number,
            'profile_picture' => $this->getFirstMediaUrl('profile_pictures') ? $this->getMedia('profile_pictures')->last()->getUrl() : asset('assets/default.jpg'),
            'provider_name' => $this->serviceProvider?->name,
            'services' => $this->whenLoaded('services', function () {
                return $this->services->map(function ($service) {
                    return [
                        'id' => $service->id,
                        'title' => $service->title,
                    ];
                });
            }),
        ];
    }
}
