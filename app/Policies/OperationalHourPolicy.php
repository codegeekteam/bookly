<?php

namespace App\Policies;

use App\Models\OperationalHour;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class OperationalHourPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, OperationalHour $operationalHours): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, OperationalHour $operationalHours): bool
    {
        return true;
    }

    public function delete(User $user, OperationalHour $operationalHours): bool
    {
        return true;
    }

    public function restore(User $user, OperationalHour $operationalHours): bool
    {
        return true;
    }

    public function forceDelete(User $user, OperationalHour $operationalHours): bool
    {
        return true;
    }
}
