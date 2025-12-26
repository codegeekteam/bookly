<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CustomerCampaignService;

class CustomerCampaignController extends Controller
{
    /**
     * getCampaign
     *
     * endpoint to get the selected campaign from the dashboard for the customer's app
     *
     * @authenticated
     *
     * @type GET
     *
     * @group Customer Campaign
     *
     * @url api/customer-campaign
     *
     * @response { "data": [ { "hot_services": [ { "id": 10, "title": "ad", "is_active": true, "description": "Explicabo nihil vitae vel rerum dolorum accusamus.", "average_price": null, "currency": "SAR", "image": "http://localhost:8001/assets/default.jpg", "created_at": "2023-10-11 12:45:23", "updated_at": "2023-10-11 12:45:23" }, { "id": 8, "title": "consequatur", "is_active": true, "description": "Architecto molestiae tempore optio sequi.", "average_price": null, "currency": "SAR", "image": "http://localhost:8001/assets/default.jpg", "created_at": "2023-10-11 12:45:23", "updated_at": "2023-10-11 12:45:23" } ], "popular_providers": [ { "id": 7, "name": "ad", "is_blocked": false, "is_active": false, "email": "judah37@example.net", "phone_number": "+1-469-319-7235", "address": null, "commercial_register": null, "twitter": null, "snapchat": null, "instagram": null, "tiktok": null, "images": "http://localhost:8001/assets/default.jpg", "attached_services": [], "provider_type": "freelancer", "created_at": "2023-10-11 12:45:23", "updated_at": "2023-10-11 12:45:23", "profile_complete_percentage": 60 }, { "id": 10, "name": "eligendi", "is_blocked": false, "is_active": true, "email": "thompson.arnaldo@example.com", "phone_number": "1-904-385-1364", "address": null, "commercial_register": null, "twitter": null, "snapchat": null, "instagram": null, "tiktok": null, "images": "http://localhost:8001/assets/default.jpg", "attached_services": [], "provider_type": "freelancer", "created_at": "2023-10-11 12:45:23", "updated_at": "2023-10-11 12:45:23", "profile_complete_percentage": 60 } ], "banners": [ "http://localhost:8001/storage/4/BpvDBFHoUHkDzbmKOxRfWmKNIgdjPB-metaMDguanBn-.jpg", "http://localhost:8001/storage/5/tf1vkWIjc0YdoGuhZyG4BrxJvLU6O0-metaMTQuanBn-.jpg", "http://localhost:8001/storage/6/sZBYdyCXkUecJ73HDn7pDhL6E4DyJ4-metaMTA5Njc2LmpwZw==-.jpg" ], "created_at": "2023-10-11T12:45:24.000000Z", "updated_at": "2023-10-12T11:36:14.000000Z" } ] }
     */
    public function getCampaign(CustomerCampaignService $customerCampaignService)
    {
        try {
            return $customerCampaignService->getCampaign();
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }

    }
}
