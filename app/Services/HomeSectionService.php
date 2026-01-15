<?php

namespace App\Services;

use App\Http\Resources\HomeSectionResource;
use App\Models\HomeSection;

class HomeSectionService
{
    public function index()
    {
        $sections=HomeSection::with('providers','providers.attachedServices','providers.providerType','providers.operationalHours')->get();
        return HomeSectionResource::collection($sections);
    }


}
