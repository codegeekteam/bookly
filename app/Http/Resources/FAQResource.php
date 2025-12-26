<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\FAQ */
class FAQResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $language = $request->header('lang') ?? 'en';

        return [
            'id' => $this->id,
            'question' => $this->getTranslation('question', $language),
            'answer' => $this->getTranslation('answer', $language),
        ];
    }
}
