<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReviewRequest;
use App\Services\ReviewService;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /**
     * get reviews
     *
     * Endpoint to return all reviews if theres is no service_id added and return reviews for specific service if service_id added
     *
     * @queryParam service_id int required
     * @queryParam service_provider_id int optional
     *
     * @url /reviews?service_id=1
     *
     * @group Review
     *
     * @authenticated
     *
     * @response { "data": [ { "id": 4, "rate": 5, "comment": "ut", "customer_name": "Gregorio Herzog", "customer_profile_picture": "http://localhost:8001/assets/default.jpg", "service_name": "repellendus", "service_image": "http://localhost:8001/assets/default.jpg", "provider_name": "incidunt", "provider_type": "freelance", "provider_profile_picture": "http://localhost:8001/assets/default.jpg", "created_at": "2023-09-28 10:21:37", "updated_at": "2023-09-28 10:21:37" },}
     */
    public function index(ReviewService $reviewService, Request $request)
    {
        try {
            return $reviewService->index($request->service_provider_id, $request->service_id);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * create review
     *
     * @url /reviews
     *
     * @type POST
     *
     * Endpoint to create a review, it allows customers to review a service only once
     *
     * @Authenticated
     *
     * @group   review
     *
     * @bodyParam rate int required
     * @bodyParam comment string optional
     * @bodyParam  appointment_id int required
     *
     * @response { "data": { "id": 11, "rate": "2", "comment": "it was bad", "customer_name": "Alexie Kub", "customer_profile_picture": "http://localhost:8001/assets/default.jpg", "service_name": "nihil", "service_image": "http://localhost:8001/assets/default.jpg", "provider_name": "cum", "provider_profile_picture": "http://localhost:8001/assets/default.jpg", "created_at": "2023-09-28 10:46:45", "updated_at": "2023-09-28 10:46:45" } }
     */
    public function create(ReviewService $reviewService, ReviewRequest $request)
    {
        try {
            return $reviewService->create(auth()->user()->customer, $request);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }
}
