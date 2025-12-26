<?php

namespace App\Policies;

use App\Models\CustomerCampaign;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CustomerCampaignPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, CustomerCampaign $customerCampaign): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, CustomerCampaign $customerCampaign): bool
    {
        return true;
    }

    public function delete(User $user, CustomerCampaign $customerCampaign): bool
    {
        return true;
    }

    public function restore(User $user, CustomerCampaign $customerCampaign): bool
    {
        return true;
    }

    public function forceDelete(User $user, CustomerCampaign $customerCampaign): bool
    {
        return true;
    }
}
