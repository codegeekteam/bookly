<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\HomeSectionService;

class HomeSectionController extends Controller
{
    /**
     * get Home Sections
     *
     * endpoint to get Home Sections
     *
     * @type GET
     *
     * @url api/home-sections
     *
     * @group home
     *
     * @response 200 [ { "id": "1", "title": "test", "providers": { "name": "Test", "id": "1" ,"image":"url"}} ]
     */
    public function index(HomeSectionService $HomeSectionService)
    {
        try {

            return $HomeSectionService->index();

        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }
}
