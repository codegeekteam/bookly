<?php

namespace App\Services;

use App\Http\Resources\CustomerCampaignResource;
use App\Models\CustomerCampaign;

class CustomerCampaignService
{
    public function getCampaign()
    {
        $campaign = CustomerCampaign::with(
            [   
                'services',    
                'services.attachedServices',
                'services.categories',
                'providers',    
                'providers.addresses',
                'providers.reviews',
                'providers.attachedServices',
              //  'providers.providerType',
              //  'providers.user.activeSubscription',
            ])
            ->where('is_active', true)->first();
        if (! $campaign) {
            return response()->json(['message' => 'no active campaign'], 404);
        }

        return new CustomerCampaignResource($campaign);

    }
}
