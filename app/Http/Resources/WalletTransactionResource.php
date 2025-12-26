<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
/**
 * @mixin \App\Models\WalletTransaction
 */
class WalletTransactionResource extends JsonResource
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
            'wallet_id' => $this->wallet_id,
            'amount' => $this->amount.'' ,
            'type' => $this->type,
            'description' => $this->getDescription($request),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    public function getDescription($request)
    {
        $desc = isset($this->description) ? $this->description : 'No description';
        if ($request->header('lang') == 'ar') {
            $desc = isset($this->description_ar) ? $this->description_ar: $desc;
        }
        return $desc;
    }
}
