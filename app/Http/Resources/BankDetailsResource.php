<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\BankDetails */
class BankDetailsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'bank_name' => $this->bank_name,
            'account_holder_name' => $this->account_holder_name,
            'iban' => $this->iban,
            'swift_code' => $this->swift_code,
            'account_number' => $this->account_number,
            'created_at' => $this->created_at,
        ];
    }
}
