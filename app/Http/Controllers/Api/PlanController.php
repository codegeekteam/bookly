<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PlanResource;
use App\Models\Plan;
use App\Services\SubscriptionService;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;

#[Group('subscriptions', 'APIs for manage subscriptions')]
class PlanController extends Controller
{
    #[Endpoint('get-plans')]
    #[Authenticated]
    #[ResponseFromApiResource(PlanResource::class, Plan::class, collection: true, with: ['items'])]
    public function __invoke(SubscriptionService $subscriptionService)
    {
        $plans = $subscriptionService->plans(auth()->user());
        return PlanResource::collection($plans);
    }
}
