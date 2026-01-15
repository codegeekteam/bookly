<?php

namespace App\Policies;

use App\Models\ServiceProvider;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ServiceProviderPolicy
{
    use HandlesAuthorization;

    public function view(User $user, ServiceProvider $serviceProvider): bool
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

    public function update(User $user, ServiceProvider $serviceProvider): bool
    {
        return true;
    }

    public function delete(User $user, ServiceProvider $serviceProvider): bool
    {
        return true;
    }

    public function restore(User $user, ServiceProvider $serviceProvider): bool
    {
        return true;
    }

    public function forceDelete(User $user, ServiceProvider $serviceProvider): bool
    {
        return true;
    }
}
