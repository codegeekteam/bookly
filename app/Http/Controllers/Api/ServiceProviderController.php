<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GetServiceProviderRequest;
use App\Http\Requests\ServiceProviderSettingsRequest;
use App\Services\ServiceProviderService;
use Illuminate\Http\Request;

class ServiceProviderController extends Controller
{
    /**
     * get provider by ID
     *
     * endpoint to get a single service provider by ID. This endpoint works for both authenticated and non-authenticated users (for sharing purposes).
     *
     * @type GET
     *
     * @url api/providers/{id}
     *
     * @group service providers
     *
     * @urlParam id integer required The ID of the service provider. Example: 94
     *
     * @response 200 { "data": { "id": 94, "name": "BioLife Salon & Spa", "is_blocked": false, "is_active": true, "email": "biolifebeautyspa@gmail.com", "phone_number": "543853030", "biography": "...", "address": null, "commercial_register": null, "twitter": null, "snapchat": null, "instagram": null, "tiktok": null, "average_rate": 0, "images": ["..."], "profile_picture": "...", "services": [...], "provider_type": "enterprise", "max_appointments_per_day": null, "deposit_type": null, "deposit_amount": null, "expected_response_time": null, "min_start_time": "00:00:00", "max_end_time": "23:00:00", "min_service_price": null, "max_service_price": null, "created_at": "2023-10-12 14:12:27", "updated_at": "2023-10-12 14:12:27", "profile_complete_percentage": 50, "remaining_profile_fields": [], "is_premium": false, "distance_km": null, "cancellation_enabled": false, "cancellation_hours_before": null } }
     * @response 404 { "message": "Provider not found" }
     * @response 403 { "message": "Provider is blocked" }
     */
    public function getProviderById(ServiceProviderService $serviceProviderService, $id)
    {
        try {
            return $serviceProviderService->getProviderById($id);
        } catch (\Exception $e) {
            $code = $e->getCode();
            if ($code == 404) {
                return $this->error($e->getMessage(), 404);
            } elseif ($code == 403) {
                return $this->error($e->getMessage(), 403);
            }
            return $this->error($e->getMessage());
        }
    }

    /**
     * get cancellation policy by provider ID
     *
     * endpoint to get the cancellation policy for a service provider
     *
     * @type GET
     *
     * @url api/providers/{id}/cancellation-policy
     *
     * @group service providers
     *
     * @urlParam id integer required The ID of the service provider. Example: 94
     *
     * @response 200 { "data": { "cancellation_enabled": true, "cancellation_hours_before": 24 } }
     * @response 404 { "message": "Provider not found" }
     * @response 403 { "message": "Provider is blocked" }
     */
    public function getCancellationPolicy(ServiceProviderService $serviceProviderService, $id)
    {
        try {
            return $serviceProviderService->getCancellationPolicy($id);
        } catch (\Exception $e) {
            $code = $e->getCode();
            if ($code == 404) {
                return $this->error($e->getMessage(), 404);
            } elseif ($code == 403) {
                return $this->error($e->getMessage(), 403);
            }
            return $this->error($e->getMessage());
        }
    }

    /**
     * get providers
     *
     * endpoint to get all service providers. When latitude and longitude are provided, providers will be sorted by nearest distance.
     *
     * @type GET
     *
     * @authenticated
     *
     * @url api/providers
     *
     * @group service providers
     *
     * @queryParam keyword string search keyword by provider's name
     * @queryParam rating integer search using average rate ranging between 1 and 5
     * @queryParam sort_direction string sort the list of providers either ascending (asc) or descending (desc) only available enums (asc,desc). Note: This is ignored when sorting by distance.
     * @queryParam latitude numeric User's latitude for distance-based sorting. Must be between -90 and 90. Example: 24.7136
     * @queryParam longitude numeric User's longitude for distance-based sorting. Must be between -180 and 180. Example: 46.6753
     *
     * @response 200 { "data": [ { "id": 1, "name": "quo", "is_blocked": false, "expected_response_tine": "Usually responds within 5 minutes", "is_active": true, "email": "re@youssef.com", "phone_number": "987654321", "biography": "hello there", "address": null, "commercial_register": null, "twitter": null, "snapchat": null, "instagram": null, "tiktok": null, "average_rate": 1.3, "distance_km": 2.5, "images": [ "http://localhost:8001/assets/default.jpg" ], "profile_picture": "http://localhost:8001/storage/1/MQzGRuAYCdmrTlCZPX4E1F0xJg7jqS-metaa2lzc3BuZy1sb2dvLWltYWdlLWNvbXB1dGVyLWljb25zLXBocC1wb3J0YWJsZS1uZXR3b3JrLWdyYS13aWxsaWFtLWRhdmllcy1tZW5nLW1vbmdvZGItNWI4ZTk2OThiMTc3MzkuMTg1OTUzMzMxNTM2MDcxMzIwNzI2OS5wbmc=-.png", "services": [ { "id": 4, "service": "beatae", "service_id": 1, "service_description": "Id autem temporibus dolor.", "currency": "SAR", "service_image": "http://localhost:8001/assets/default.jpg", "service_provider": "quo", "service_provider_type": "freelancer", "service_provider_image": "http://localhost:8001/assets/default.jpg", "price": 200, "min_price": 200, "max_price": 200, "service_provider_id": 1, "average_rate": null, "my_place": true, "customer_place": false } ], "provider_type": "freelancer", "created_at": "2023-10-12 14:12:27", "updated_at": "2023-10-12 15:23:41", "profile_complete_percentage": 83 }, { "id": 3, "name": "quaerat", "is_blocked": false, "expected_response_tine": "Usually responds within 5 minutes", "is_active": true, "email": "hcarroll@example.com", "phone_number": "(551) 540-0652", "biography": null, "address": null, "commercial_register": null, "twitter": null, "snapchat": null, "instagram": null, "tiktok": null, "average_rate": 2, "distance_km": 5.8, "images": [ "http://localhost:8001/assets/default.jpg" ], "profile_picture": "http://localhost:8001/assets/default.jpg", "services": [], "provider_type": "freelancer", "created_at": "2023-10-12 14:12:27", "updated_at": "2023-10-12 14:12:27", "profile_complete_percentage": 50 }, { "id": 5, "name": "laborum", "is_blocked": false, "is_active": true, "email": "vboehm@example.org", "phone_number": "754.460.1422", "biography": null, "address": null, "commercial_register": null, "twitter": null, "snapchat": null, "instagram": null, "tiktok": null, "average_rate": 5, "images": [ "http://localhost:8001/assets/default.jpg" ], "profile_picture": "http://localhost:8001/assets/default.jpg", "services": [], "provider_type": "freelancer", "created_at": "2023-10-12 14:12:27", "updated_at": "2023-10-12 14:12:27", "profile_complete_percentage": 50 }, { "id": 8, "name": "sit", "is_blocked": false, "is_active": true, "email": "joseph73@example.net", "phone_number": "(540) 776-3277", "biography": null, "address": null, "commercial_register": null, "twitter": null, "snapchat": null, "instagram": null, "tiktok": null, "average_rate": 0, "images": [ "http://localhost:8001/assets/default.jpg" ], "profile_picture": "http://localhost:8001/assets/default.jpg", "services": [], "provider_type": "freelancer", "created_at": "2023-10-12 14:12:27", "updated_at": "2023-10-12 14:12:27", "profile_complete_percentage": 50 } ] }
     */
    public function getProviders(ServiceProviderService $serviceProviderService, GetServiceProviderRequest $request)
    {
        try {
            return $serviceProviderService->getProviders($request);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * change service provider settings
     *
     * endpoint to change service provider settings
     *
     * @type POST
     *
     * @url api/providers/
     *
     * @group service providers
     *
     * @authenticated
     *
     * @response 200 { "data": { "id": 1, "name": "rem", "is_blocked": true, "is_active": false, "email": "re@youssef.com", "phone_number": "987654321", "biography": "hello there", "address": null, "commercial_register": null, "twitter": null, "snapchat": null, "instagram": null, "tiktok": null, "average_rate": 3, "images": [ "http://localhost:8000/assets/default.jpg" ], "profile_picture": "http://localhost:8000/assets/default.jpg", "services": [], "provider_type": "freelancer", "max_appointments_per_day": 5, "deposit_type": "fixed", "deposit_amount": 200, "created_at": "2023-12-12 16:04:56", "updated_at": "2023-12-13 08:36:52", "profile_complete_percentage": 90 } }
     */
    public function changeServiceProviderSettings(
        ServiceProviderService $authService,
        ServiceProviderSettingsRequest $request
    ) {
        try {
            return $authService->changeServiceProviderSettings(
                auth()->user()->serviceProvider,
                $request->max_appointments_per_day,
                $request->deposit_type,
                $request->deposit_amount,
                $request->cancellation_enabled,
                $request->cancellation_hours_before,
                $request->minimum_booking_lead_time_hours,
                $request->maximum_booking_lead_time_months
            );
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * count of confirmed appointments
     *
     * endpoint to get count of confirmed appointments
     *
     * @type GET
     *
     * @authenticated
     *
     * @group service providers
     *
     * @url api/providers/dashboard/appointments/confirmed/count
     *
     * @response 200 2
     */
    public function countOfConfirmedAppointments(ServiceProviderService $serviceProviderService)
    {
        try {
            return $serviceProviderService->countOfConfirmedAppointments(serviceProvider: auth()->user()->serviceProvider);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * count of rejected appointments
     *
     * endpoint to get count of confirmed appointments
     *
     * @type GET
     *
     * @authenticated
     *
     * @group service providers
     *
     * @url api/providers/dashboard/appointments/rejected/count
     *
     * @response 200 2
     */
    public function countOfRejectedAppointments(ServiceProviderService $serviceProviderService)
    {
        try {
            return $serviceProviderService->countOfRejectedAppointments(serviceProvider: auth()->user()->serviceProvider);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * count of Used Gift Cards
     *
     * endpoint to get count of Used Gift Cards on service provider appointments
     *
     * @type GET
     *
     * @authenticated
     *
     * @group service providers
     *
     * @url api/providers/dashboard/used-gift-cards/count
     *
     * @response 200 2
     */
    public function countOfUsedGiftCards(ServiceProviderService $serviceProviderService)
    {
        try {
            return $serviceProviderService->countOfUsedGiftCards(serviceProvider: auth()->user()->serviceProvider);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * count of cancelled appointments
     *
     * endpoint to get the amount of cancelled appointments
     *
     * @type GET
     *
     * @group service providers
     *
     * @authenticated
     *
     * @url api/providers/dashboard/appointments/cancelled/count
     *
     * @response 200 2
     */
    public function countOfCancelledAppointments(ServiceProviderService $serviceProviderService)
    {
        try {
            return $serviceProviderService->countOfCancelledAppointments(serviceProvider: auth()->user()->serviceProvider);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * count of bookings per day
     *
     * endpoint to get the amount of booked (confirmed) appointments per day
     *
     * <aside class="notice">the params are set to be a range of dates. If the date_to is set to be after date_from, it will throw a validation error</aside>
     *
     * @type GET
     *
     * @group service providers
     *
     * @authenticated
     *
     * @url api/providers/dashboard/bookings/count
     *
     * @queryParam date_from string date from if not added it will be set to current date
     * @queryParam date_to string date to  if not added it will be set a day after date from by a week
     *
     * @response 200 { "2021-10-12": 2, "2021-10-13": 1 }
     */
    public function countOfBookingsPerDay(ServiceProviderService $serviceProviderService, Request $request)
    {
        try {
            return $serviceProviderService->countOfBookingsPerDay(
                serviceProvider: auth()->user()->serviceProvider,
                date_from: $request->date_from,
                date_to: $request->date_to
            );

        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * get total earnings
     *
     * endpoint to get the total earnings for the service provider
     *
     * @group service providers
     *
     * @url api/providers/dashboard/total-earnings
     *
     * @authenticated
     *
     * @queryParam period enum period to get the total earnings for the current month (m), week (w), year (y) default is month options (m,w,y)
     *
     * @response 200 1500
     */
    public function getTotalEarnings(ServiceProviderService $serviceProviderService, Request $request)
    {
        try {
            return $serviceProviderService->getTotalEarnings(
                serviceProvider: auth()->user()->serviceProvider,
                period: $request->period
            );

        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * get the most booked service
     *
     * endpoint to get the most booked service for the service provider
     *
     * @type  GET
     *
     * @authenticated
     *
     * @url api/providers/dashboard/services/most-booked
     *
     * @group service providers
     *
     * @response 200 [ { "service_name": "dicta", "booking_count": 2 }, { "service_name": "quia", "booking_count": 1 } ]
     */
    public function getTheMostBookedService(ServiceProviderService $serviceProviderService)
    {

        try {
            return $serviceProviderService->getTheMostBookedService(
                serviceProvider: auth()->user()->serviceProvider
            );
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }

    }

    /**
     * get service provider dashboard info
     *
     * endpoint to get service provider dashboard info
     * <aside class="notice">the params are set to be a range of dates. If the date_to is set to be after date_from, it will throw a validation error</aside>
     * <aside class="notice">**IMPORTANT NOTE** Please be advised that each property in that response has its own separate endpoint in case if its needed separately.</aside>
     *
     * @type GET
     *
     * @group service providers
     *
     * @authenticated
     *
     * @url api/providers/dashboard
     *
     * @queryParam date_from string date from if not added it will be set to current date format (d-m-Y) example: 12-10-2021
     * @queryParam date_to string date to  if not added it will be set a day after date from by a week (d-m-Y) example: 12-11-2021
     * @queryParam period enum period to get the total earnings for the current month (m), week (w), year (y) default is month options (m,w,y)
     *
     * @response 200 { "count_of_confirmed_appointments": 0, "count_of_used_gift_cards": 0, "count_of_cancelled_appointments": 0, "count_of_bookings_per_day": { "26/12/2023": 0, "27/12/2023": 0, "28/12/2023": 0, "29/12/2023": 0, "30/12/2023": 0 }, "most_booked_service": [ { "service_name": "dicta", "booking_count": 2 }, { "service_name": "quia", "booking_count": 1 } ], "most_preferred_service_type": [ { "service_type_name": "My Place", "service_type_count": 2 }, { "service_type_name": "Customer's Place", "service_type_count": 1 } ], "total_earnings": 0 }
     */
    public function getServiceProviderDashboardInfo(ServiceProviderService $serviceProviderService, Request $request)
    {
        try {
            return $serviceProviderService->getServiceProviderDashboardInfo(
                serviceProvider: auth()->user()->serviceProvider,
                date_from: $request->date_from,
                date_to: $request->date_to,
                period: $request->period
            );

        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * get the most preferred service type
     *
     * endpoint to get the most preferred service type for the service provider
     *
     * @type GET
     *
     * @group service providers
     *
     * @url api/providers/dashboard/delivery-types/most-preferred
     *
     * @authenticated
     *
     * @response 200 [{ "service_type_name": "My Place", "booking_count": 2 } , { "service_type_name": "Customer Place", "booking_count": 1 }]
     */
    public function getTheMostPreferredServiceType(ServiceProviderService $serviceProviderService)
    {

        try {

            return $serviceProviderService->getTheMostPreferredServiceType(
                serviceProvider: auth()->user()->serviceProvider
            );

        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }

    }

    /**
     * Get deferred payouts (not yet available)
     *
     * Get list of pending payout amounts that are not yet available for transfer
     *
     * @group Service Provider Payouts
     * @authenticated
     */
    public function getDeferredPayouts(\App\Services\PayoutService $payoutService)
    {
        try {
            $providerId = auth()->user()->serviceProvider->id;
            $summary = $payoutService->getProviderDeferredSummary($providerId);

            return $this->success([
                'deferred_payouts' => \App\Http\Resources\DeferredPayoutResource::collection($summary['not_yet_available']),
            ]);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * Get deferred payouts total
     *
     * Get total amount of pending payouts not yet available
     *
     * @group Service Provider Payouts
     * @authenticated
     */
    public function getDeferredPayoutsTotal(\App\Services\PayoutService $payoutService)
    {
        try {
            $providerId = auth()->user()->serviceProvider->id;
            $summary = $payoutService->getProviderDeferredSummary($providerId);

            return $this->success([
                'total' => $summary['not_yet_available_total'],
            ]);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * Get available payouts
     *
     * Get list of payout amounts available but not yet transferred
     *
     * @group Service Provider Payouts
     * @authenticated
     */
    public function getAvailablePayouts(\App\Services\PayoutService $payoutService)
    {
        try {
            $providerId = auth()->user()->serviceProvider->id;
            $summary = $payoutService->getProviderDeferredSummary($providerId);

            return $this->success([
                'available_payouts' => \App\Http\Resources\DeferredPayoutResource::collection($summary['available']),
            ]);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * Get available payouts total
     *
     * Get total amount of payouts available for transfer
     *
     * @group Service Provider Payouts
     * @authenticated
     */
    public function getAvailablePayoutsTotal(\App\Services\PayoutService $payoutService)
    {
        try {
            $providerId = auth()->user()->serviceProvider->id;
            $summary = $payoutService->getProviderDeferredSummary($providerId);

            return $this->success([
                'total' => $summary['available_total'],
            ]);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * Get transferred payouts history
     *
     * Get history of all transferred payouts
     *
     * @group Service Provider Payouts
     * @authenticated
     */
    public function getTransferredPayouts(\App\Services\PayoutService $payoutService)
    {
        try {
            $providerId = auth()->user()->serviceProvider->id;
            $payouts = $payoutService->getProviderTransferredHistory($providerId);

            return $this->success([
                'transferred_payouts' => \App\Http\Resources\PayoutHistoryResource::collection($payouts),
            ]);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }
}
