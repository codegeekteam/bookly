<?php

namespace App\Services;

use App\Http\Requests\ReviewRequest;
use App\Http\Resources\ReviewResource;
use App\Models\Customer;
use App\Models\Review;

class ReviewService
{
    public function index(?int $service_provider_id, int $service_id)
    {
        $reviews = Review::where('service_id', $service_id);

        if ($service_provider_id) {
            $reviews = $reviews->where('service_provider_id', $service_provider_id);
        }

        return ReviewResource::collection($reviews->get());

    }

    public function create(Customer $customer, ReviewRequest $request)
    {

        if ($customer->reviews()->where('appointment_id', $request->appointment_id)->exists()) {
            throw new \Exception('You already reviewed this service');
        }

        $appointment = $customer->appointments()->where('id', $request->appointment_id)->with(['services'])->first();

        foreach ($appointment->services as $service) {
            $review = $appointment->review()->create([
                'rate' => $request->get('rate'),
                'comment' => $request->get('comment'),
                'customer_id' => $customer->id,
                'service_id' => $service->id,
                'service_provider_id' => $appointment->service_provider_id,
                'appointment_id' => $appointment->id,
            ]);
        }

        return new ReviewResource($review);
    }
}
