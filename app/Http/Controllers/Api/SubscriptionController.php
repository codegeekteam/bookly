<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubscriptionRequest;
use App\Http\Resources\SubscriptionResource;
use App\Models\Plan;
use App\Models\Subscription;
use App\Services\SubscriptionService;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;

#[Group('subscriptions', 'APIs for manage subscriptions')]
class SubscriptionController extends Controller
{
    #[Endpoint('get-active-subscription')]
    #[Authenticated]
    #[ResponseFromApiResource(SubscriptionResource::class, Subscription::class, with: ['plan','plan.items'])]
    public function activeSubscription(SubscriptionService $subscriptionService)
    {
        try {
            return $subscriptionService->activeSubscription(auth()->user());

        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    #[Endpoint('create-subscription')]
    #[Authenticated]
    #[ResponseFromApiResource(SubscriptionResource::class, Subscription::class, with: ['plan','plan.items'])]
    public function create(SubscriptionService $subscriptionService,SubscriptionRequest $request)
    {
        try {
            return $subscriptionService->create(auth()->user(), $request);

        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

}
