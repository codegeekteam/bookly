<?php

namespace App\Policies;

use App\Models\Subscription;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SubscriptionPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Subscription $subscription): bool
    {
        return $user->id === $subscription->user_id;
    }

    public function create(User $user): bool
    {
        return $user->serviceProvider?->is_active ?? true;
    }

    public function update(User $user, Subscription $subscription): bool
    {
        return $user->id === $subscription->user_id;
    }

    public function delete(User $user, Subscription $subscription): bool
    {
        return $user->id === $subscription->user_id;
    }

    public function restore(User $user, Subscription $subscription): bool
    {
        return true;
    }

    public function forceDelete(User $user, Subscription $subscription): bool
    {
        return true;
    }
}
