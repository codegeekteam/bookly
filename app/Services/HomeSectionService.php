<?php

namespace App\Services;

use App\Http\Resources\HomeSectionResource;
use App\Models\HomeSection;

class HomeSectionService
{
    public function index()
    {
        $sections=HomeSection::with(
            [   'providers' => function ($q) {
                    $q->withAvg('reviews', 'rate');
                },
                'providers.attachedServices',
                'providers.providerType',
                'providers.operationalHours',     
                'providers.reviews',
                'providers.user.activeSubscription',
                'providers.address',
                'providers.user.activeSubscription',
            ])
            ->get();
        return HomeSectionResource::collection($sections);
    }


}
