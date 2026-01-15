<?php

namespace App\Services;

use App\Http\Resources\ProviderTypeResource;
use App\Models\ProviderType;

class ProviderTypeService
{
    public function index()
    {
        $providers = ProviderType::all();

        return ProviderTypeResource::collection($providers);
    }
}
