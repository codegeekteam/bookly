<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookedServiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->service?->getTranslation('title', $request->header('lang') ?? 'en'),
            'price' => $this->price,
            'number_of_beneficiaries' => rand(1, 6),
            'selected_employee' => null,
            'start_time' => '9:00AM',
            'end_time' => '10:00AM',
        ];
    }
}
