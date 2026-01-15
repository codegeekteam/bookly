<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ProviderTypeService;

class ProviderTypeController extends Controller
{
    /**
     * get provider types
     *
     *  endpoint to get all provider types
     *
     * @group provider types
     *
     * @type GET
     *
     * @url api/providers/types
     *
     * @authenticated
     *
     * @response 200 { "data": [ { "id": 1, "title": "freelancer" }, { "id": 2, "title": "enterprise" } ] }
     */
    public function index(ProviderTypeService $providerTypeService)
    {
        try {
            return $providerTypeService->index();

        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }
}
