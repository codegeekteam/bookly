<?php

namespace App\Observers;

use App\Models\ServiceProvider;
use App\Models\User;

class ServiceProviderObserver
{
    /**
     * Handle the ServiceProvider "created" event.
     */
    public function created(ServiceProvider $serviceProvider): void
    {
        $user = User::create(
            [
                'name' => $serviceProvider->name,
                'email' => $serviceProvider->email,
                'password' => bcrypt('password'),
            ]
        );
        $serviceProvider->update(['user_id' => $user->id]);
    }

    /**
     * Handle the ServiceProvider "updated" event.
     */
    public function updated(ServiceProvider $serviceProvider): void
    {
        //
    }

    /**
     * Handle the ServiceProvider "deleted" event.
     */
    public function deleted(ServiceProvider $serviceProvider): void
    {
        //
    }

    /**
     * Handle the ServiceProvider "restored" event.
     */
    public function restored(ServiceProvider $serviceProvider): void
    {
        //
    }

    /**
     * Handle the ServiceProvider "force deleted" event.
     */
    public function forceDeleted(ServiceProvider $serviceProvider): void
    {
        //
    }
}
