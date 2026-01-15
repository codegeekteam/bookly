<?php

namespace App\Observers;

use App\Models\Cart;
use App\Models\Customer;
use App\Models\User;

class CustomerObserver
{
    public function created(Customer $customer): void
    {
        $user = User::create(
            [
                'name' => $customer->first_name.' '.$customer->last_name,
                'email' => $customer->email,
                'password' => bcrypt('password'),
            ]
        );
        $customer->update(['user_id' => $user->id]);
        Cart::create(['customer_id' => $customer->id]);
    }

    public function updated(Customer $customer): void
    {
    }

    public function deleted(Customer $customer): void
    {
    }

    public function restored(Customer $customer): void
    {
    }

    public function forceDeleted(Customer $customer): void
    {
    }
}
