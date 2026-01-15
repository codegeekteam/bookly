<?php

namespace App\Observers;

use App\Models\Employee;
use App\Models\User;

class EmployeeObserver
{
    /**
     * Handle the Employee "created" event.
     */
    public function created(Employee $employee): void
    {
        $user = User::create(
            [
                'name' => $employee->name,
                'email' => $employee->email,
                'password' => bcrypt('password'),
            ]
        );
        $employee->update(['user_id' => $user->id]);
    }

    /**
     * Handle the Employee "updated" event.
     */
    public function updated(Employee $employee): void
    {
        $employee->user->update(
            [
                'name' => $employee->name,
                'email' => $employee->email,
            ]
        );
    }

    /**
     * Handle the Employee "deleted" event.
     */
    public function deleted(Employee $employee): void
    {
        //
    }

    /**
     * Handle the Employee "restored" event.
     */
    public function restored(Employee $employee): void
    {
        //
    }

    /**
     * Handle the Employee "force deleted" event.
     */
    public function forceDeleted(Employee $employee): void
    {
        //
    }
}
