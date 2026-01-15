<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
/**
 * @mixin \App\Models\WalletTransaction
 */
class PointTransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request)
    {
        return [
            'id' => $this->id,
            'customer_id' => $this->customer_id,
            'points' => $this->points.'' ,
            'type' => $this->type,
            'description' => $this->description,
            'created_at' => $this->created_at,
        ];
    }
}
