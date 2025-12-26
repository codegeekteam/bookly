<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Plan */
class PlanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'number_of_months' =>$this->number_of_months,
            'image' =>$this->image_url,
            'price' => $this->price,
            'items_count' => $this->items_count,
            'items' => PlanItemResource::collection($this->items),
        ];
    }
}
