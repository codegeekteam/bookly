<?php

namespace App\Observers;

use App\Models\ServiceProvider;
use App\Models\User;
use App\Notifications\ServiceProviderApprovedNotification;
use Illuminate\Support\Facades\DB;
use App\Mail\ServiceProviderApprovedMail;
use Illuminate\Support\Facades\Mail;

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
           if ($serviceProvider->wasChanged('is_active') && $serviceProvider->getOriginal('is_active') == false && $serviceProvider->is_active == true)
            {
                if ($serviceProvider->user) 
                {
                    if($serviceProvider->email) {
                        Mail::to($serviceProvider->email)->send(New ServiceProviderApprovedMail($serviceProvider, true)); 
                    }
                   $serviceProvider->user->notify(new ServiceProviderApprovedNotification($serviceProvider, true));
                }
            }
            if ($serviceProvider->wasChanged('is_active') && $serviceProvider->getOriginal('is_active') == true && $serviceProvider->is_active == false)
            {
                if ($serviceProvider->user) 
                {                   
                    $email = $serviceProvider->email;
                    $serviceProvider_copy = $serviceProvider;                         
                    $serviceProvider->user->notify(new ServiceProviderApprovedNotification($serviceProvider, false));                   
                  
                    if($email) {
                         Mail::to($email)->queue(New ServiceProviderApprovedMail($serviceProvider_copy, false)); 
                    }     
                    // revoke sanctum tokens                            
                   $serviceProvider->user->tokens()->delete(); 
                     
                }
            }
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
