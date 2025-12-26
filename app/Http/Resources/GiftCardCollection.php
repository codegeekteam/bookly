<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

/** @see \App\Models\GiftCard */
class GiftCardCollection extends ResourceCollection
{
    public static $wrap = null;

    public function toArray(Request $request)
    {
        return GiftCardResource::collection($this->collection);
    }
}
