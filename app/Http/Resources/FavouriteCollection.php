<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

/** @see \App\Models\Favourite */
class FavouriteCollection extends ResourceCollection
{
    public static $wrap = null;

    public function toArray(Request $request)
    {
        return FavouriteResource::collection($this->collection);
    }
}
