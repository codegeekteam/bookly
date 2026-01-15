<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

/** @see \App\Models\FAQ */
class FAQCollection extends ResourceCollection
{
    public static $wrap = '';

    public function toArray(Request $request)
    {
        return FAQResource::collection($this->collection);
    }
}
