<?php

namespace App\Policies;

use App\Models\Review;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ReviewPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Review $review): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Review $review): bool
    {
        return true;
    }

    public function delete(User $user, Review $review): bool
    {
        return true;
    }

    public function restore(User $user, Review $review): bool
    {
        return true;
    }

    public function forceDelete(User $user, Review $review): bool
    {
        return true;
    }
}
