<?php

namespace App\Policies;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class EmployeePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Employee $employee): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Employee $employee): bool
    {
        return true;
    }

    public function delete(User $user, Employee $employee): bool
    {
        return true;
    }

    public function restore(User $user, Employee $employee): bool
    {
        return true;
    }

    public function forceDelete(User $user, Employee $employee): bool
    {
        return true;
    }
}
