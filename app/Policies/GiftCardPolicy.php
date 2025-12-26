<?php

namespace App\Policies;

use App\Models\GiftCard;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class GiftCardPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, GiftCard $giftCard): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, GiftCard $giftCard): bool
    {
        return true;
    }

    public function delete(User $user, GiftCard $giftCard): bool
    {
        return true;
    }

    public function restore(User $user, GiftCard $giftCard): bool
    {
        return true;
    }

    public function forceDelete(User $user, GiftCard $giftCard): bool
    {
        return true;
    }
}
