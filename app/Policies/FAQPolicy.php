<?php

namespace App\Policies;

use App\Models\FAQ;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class FAQPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, FAQ $FAQ): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, FAQ $FAQ): bool
    {
        return true;
    }

    public function delete(User $user, FAQ $FAQ): bool
    {
        return true;
    }

    public function restore(User $user, FAQ $FAQ): bool
    {
        return true;
    }

    public function forceDelete(User $user, FAQ $FAQ): bool
    {
        return true;
    }
}
