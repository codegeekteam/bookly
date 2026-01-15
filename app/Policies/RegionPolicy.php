<?php

namespace App\Policies;

use App\Models\Region;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RegionPolicy
{
    use HandlesAuthorization;

    public function view(User $user, Region $region): bool
    {
        return true;
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Region $region): bool
    {
        return true;
    }

    public function delete(User $user, Region $region): bool
    {
        return true;
    }

    public function restore(User $user, Region $region): bool
    {
        return true;
    }

    public function forceDelete(User $user, Region $region): bool
    {
        return true;
    }
}
