<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use App\Models\Address;
use App\Models\Appointment;
use App\Models\Category;
use App\Models\Customer;
use App\Models\CustomerCampaign;
use App\Models\DeliveryType;
use App\Models\Employee;
use App\Models\FAQ;
use App\Models\Favourite;
use App\Models\GiftCard;
use App\Models\OperationalHour;
use App\Models\ProviderType;
use App\Models\Region;
use App\Models\Review;
use App\Models\Service;
use App\Models\ServiceDeliveryTypes;
use App\Models\Subscription;
use App\Policies\AddressPolicy;
use App\Policies\AppointmentPolicy;
use App\Policies\CategoryPolicy;
use App\Policies\CustomerCampaignPolicy;
use App\Policies\CustomerPolicy;
use App\Policies\DeliveryTypePolicy;
use App\Policies\EmployeePolicy;
use App\Policies\FAQPolicy;
use App\Policies\FavouritePolicy;
use App\Policies\GiftCardPolicy;
use App\Policies\OperationalHourPolicy;
use App\Policies\ProviderTypePolicy;
use App\Policies\RegionPolicy;
use App\Policies\ReviewPolicy;
use App\Policies\ServicePolicy;
use App\Policies\ServiceProviderPolicy;
use App\Policies\SubscriptionPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
        Category::class => CategoryPolicy::class,
        Region::class => RegionPolicy::class,
        Customer::class => CustomerPolicy::class,
        Service::class => ServicePolicy::class,
        App\Models\ServiceProvider::class => ServiceProviderPolicy::class,
        ProviderType::class => ProviderTypePolicy::class,
        ServiceDeliveryTypes::class => ServiceDeliveryTypesPolicy::class,
        DeliveryType::class => DeliveryTypePolicy::class,
        Employee::class => EmployeePolicy::class,
        Address::class => AddressPolicy::class,
        Review::class => ReviewPolicy::class,
        Appointment::class => AppointmentPolicy::class,
        OperationalHour::class => OperationalHourPolicy::class,
        CustomerCampaign::class => CustomerCampaignPolicy::class,
        GiftCard::class => GiftCardPolicy::class,
        Favourite::class => FavouritePolicy::class,
        FAQ::class => FAQPolicy::class,
        Subscription::class => SubscriptionPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
