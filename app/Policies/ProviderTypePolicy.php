<?php

namespace App\Policies;

use App\Models\ProviderType;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProviderTypePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, ProviderType $providerType): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, ProviderType $providerType): bool
    {
        return true;
    }

    public function delete(User $user, ProviderType $providerType): bool
    {
        return true;
    }

    public function restore(User $user, ProviderType $providerType): bool
    {
        return true;
    }

    public function forceDelete(User $user, ProviderType $providerType): bool
    {
        return true;
    }
}
