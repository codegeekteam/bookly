<?php

namespace App\Services;

use App\Http\Resources\HomeSectionResource;
use App\Models\HomeSection;
use Illuminate\Support\Facades\DB;

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
               // 'providers.addresses',
            ])
            ->get();
$start = microtime(true);

$json = HomeSectionResource::collection($sections)->response()->getData(true);

dump('Serialization', microtime(true) - $start);
        return HomeSectionResource::collection($sections);
    }


}
