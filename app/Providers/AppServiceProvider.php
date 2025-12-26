<?php

namespace App\Providers;

use App\Http\Resources\CustomerResource;
use App\Models\Appointment;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\PointTransaction;
use App\Models\Review;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Observers\AppointmentObserver;
use App\Observers\CustomerObserver;
use App\Observers\EmployeeObserver;
use App\Observers\PointTransactionObserver;
use App\Observers\ReviewObserver;
use App\Observers\ServiceProviderObserver;
use App\Observers\UserObserver;
use App\Observers\WalletTransactionObserver;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Appointment::observe(AppointmentObserver::class);
        Customer::observe(CustomerObserver::class);
        \App\Models\ServiceProvider::observe(ServiceProviderObserver::class);
        Employee::observe(EmployeeObserver::class);
        // AttachedService::observe(AttachedServiceObserver::class);
        CustomerResource::withoutWrapping();
        Review::observe(ReviewObserver::class);
        User::observe(UserObserver::class);
        WalletTransaction::observe(WalletTransactionObserver::class);
        PointTransaction::observe(PointTransactionObserver::class);
        TextColumn::macro('truncate', function ($length = 50) {
            $this->formatStateUsing(function ($state) use ($length) {
                return strlen($state) > $length ? substr($state, 0, $length).'...' : $state;
            });

            return $this;
        });

          //by sreeja
        \Illuminate\Database\Eloquent\Relations\Relation::morphMap([
            'customer' => \App\Models\Customer::class,
            'provider' => \App\Models\ServiceProvider::class,
        ]);
    }
}
