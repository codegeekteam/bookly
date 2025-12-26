<?php

namespace App\Policies;

use App\Models\Favourite;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class FavouritePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->customer != null;
    }

    public function view(User $user, Favourite $favourite): bool
    {
        return $user->customer->id == $favourite->customer_id;
    }

    public function create(User $user): bool
    {
        return $user->customer != null;
    }

    public function update(User $user, Favourite $favourite): bool
    {
        return $user->customer->id == $favourite->customer_id;
    }

    public function delete(User $user, Favourite $favourite): bool
    {
        return $user->customer->id == $favourite->customer_id;
    }

    public function restore(User $user, Favourite $favourite): bool
    {
        return $user->customer->id == $favourite->customer_id;
    }

    public function forceDelete(User $user, Favourite $favourite): bool
    {
        return $user->customer->id == $favourite->customer_id;
    }
}
