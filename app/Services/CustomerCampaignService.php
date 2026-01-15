<?php

namespace App\Services;

use App\Http\Resources\CustomerCampaignResource;
use App\Models\CustomerCampaign;

class CustomerCampaignService
{
    public function getCampaign()
    {
        $campaign = CustomerCampaign::where('is_active', true)->first();

        if (! $campaign) {
            return response()->json(['message' => 'no active campaign'], 404);
        }

        return new CustomerCampaignResource($campaign);

    }
}
