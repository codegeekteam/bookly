<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\GiftCardTheme */
class GiftCardThemeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // Retrieve title in the current locale
        $title = $this->getTranslation('title', app()->getLocale());

        return [
            'id' => $this->id,
            'title' => $title, // Return the translated title based on the current locale
            'main_image' => $this->main_image, // Main image URL
            'active' => $this->active??0,
        ];
    }
}
