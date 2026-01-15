<?php

namespace App\Services;

use App\Http\Resources\SubscriptionResource;
use App\Models\Plan;
use App\Models\User;

use Exception;


class SubscriptionService
{
    /**
     * @throws Exception
     */
    public function activeSubscription(User $user)
    {
        if (!$user->serviceProvider()) {
            throw new Exception(__('user should be service provider'));
        }

        $subscription = $user->activeSubscription;

        if (!$subscription) {
            throw new Exception(__('no data found'));
        }
        $subscription->load('plan', 'plan.items');
        return response()->json(SubscriptionResource::make($subscription), 200);
    }


    public function create(User $user, $request)
    {
        //check user type
        if (!$user->serviceProvider()) {
            throw new Exception(__('user should be service provider'));
        }
        //check plan
        $plan = $this->findPlan($request);
        if (!$plan) {
            throw new Exception(__('plan not found'));
        }
        //check free plan
        if ($plan->price == 0) {
            if ($this->checkFreePlan($user)) {
                throw new Exception(__('you subscribed to free plan before'));
            }
        }
        //calculate expires_at
        $expiresAt = now()->addMonths($plan->number_of_months);

        $subscription = $user->subscriptions()->create([
            'plan_id' => $request->plan_id,
            'amount_paid' => $plan->price,
            'expires_at' => $expiresAt,
            'start_date' => now(),
            'payment_status' => $plan->price == 0 ? 'paid' : 'unpaid',
        ]);

        return response()->json(SubscriptionResource::make($subscription), 200);
    }

    public function findPlan($request)
    {
        return Plan::where('active', 1)->where('id', $request->plan_id)->first();
    }

    public function checkFreePlan($user)
    {
        return $user->subscriptions()
            ->where('amount_paid', 0)
            ->exists();
    }

    public function plans($user)
    {
        $plans = Plan::where('active', true)->with('items')->withCount('items')->get();

        //check if user subscribed to free plan before
        if ($this->checkFreePlan($user)) {
            $plans = Plan::where('active', true)->where('price', '>', 0)->with('items')->withCount('items')->get();
        }

        return $plans;
    }

}
