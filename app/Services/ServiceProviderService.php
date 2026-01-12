<?php

namespace App\Services;

use App\Enums\AppointmentStatus;
use App\Http\Resources\ServiceProviderResource;
use App\Models\ServiceProvider;
use Illuminate\Support\Carbon;

class ServiceProviderService
{
    public function getProviders($request)
    {
        $latitude = $request->latitude;
        $longitude = $request->longitude;

        $query = ServiceProvider::query()
            ->with('addresses')
            ->when($request->filled('rating'), function ($query) use ($request) {
                $query->where('average_rating', '>=', $request->rating);
            })
            ->when($request->has('keyword'), function ($query) use ($request) {
                $keyword = '%'.$request->keyword.'%';
                $query->where('name', 'LIKE', $keyword);
            })
            ->where('is_active', true)
            ->where('published', true);


        // If latitude and longitude are provided, sort by distance
        if ($latitude && $longitude) {
            $providers = $query->get()->map(function ($provider) use ($latitude, $longitude) {
                // Get the provider's default address or first address
                $address = $provider->addresses()
                    ->where('is_default', true)
                    ->first() ?? $provider->addresses()->first();

                if ($address && $address->latitude && $address->longitude) {
                    // Calculate distance using Haversine formula
                    $provider->distance = $this->calculateDistance(
                        $latitude,
                        $longitude,
                        $address->latitude,
                        $address->longitude
                    );
                } else {
                    // If no address with coordinates, set a large distance
                    $provider->distance = PHP_FLOAT_MAX;
                }

                return $provider;
            });

            // Sort by distance
            $providers = $providers->sortBy('distance')->values();
        } else {
            // Default sorting by name
            $providers = $query->orderBy('name', $request->sort_direction ?? 'asc')->get();
        }

        if ($providers->isEmpty()) {
            throw new \Exception('No providers found');
        }

        return ServiceProviderResource::collection($providers);
    }

    /**
     * Get a single service provider by ID
     *
     * @param int $id
     * @return ServiceProviderResource
     * @throws \Exception
     */
    public function getProviderById($id)
    {
        $provider = ServiceProvider::find($id);

        if (!$provider) {
            throw new \Exception('Provider not found', 404);
        }

        if ($provider->is_blocked) {
            throw new \Exception('Provider is blocked', 403);
        }

        return new ServiceProviderResource($provider);
    }

    public function getCancellationPolicy($id)
    {
        $provider = ServiceProvider::find($id);

        if (!$provider) {
            throw new \Exception('Provider not found', 404);
        }

        if ($provider->is_blocked) {
            throw new \Exception('Provider is blocked', 403);
        }

        return response()->json([
            'data' => [
                'cancellation_enabled' => $provider->cancellation_enabled ?? false,
                'cancellation_hours_before' => $provider->cancellation_hours_before,
            ]
        ]);
    }

    /**
     * Calculate distance between two coordinates using Haversine formula
     *
     * @param  float  $lat1
     * @param  float  $lon1
     * @param  float  $lat2
     * @param  float  $lon2
     * @return float Distance in kilometers
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // Earth radius in kilometers

        $latDiff = deg2rad($lat2 - $lat1);
        $lonDiff = deg2rad($lon2 - $lon1);

        $a = sin($latDiff / 2) * sin($latDiff / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($lonDiff / 2) * sin($lonDiff / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    public function changeServiceProviderSettings(
        ServiceProvider $serviceProvider,
        ?int $max_appointments_per_day,
        ?string $deposit_type,
        ?int $deposit_amount,
        ?bool $cancellation_enabled = null,
        ?int $cancellation_hours_before = null,
        ?int $minimum_booking_lead_time_hours = null,
        ?int $maximum_booking_lead_time_months = null
    ): ServiceProviderResource {
        $serviceProvider->update([
            'max_appointments_per_day' => $max_appointments_per_day ?? $serviceProvider->max_appointments_per_day,
            'deposit_type' => $deposit_type ?? $serviceProvider->deposit_type,
            'deposit_amount' => $deposit_amount ?? $serviceProvider->deposit_amount,
            'cancellation_enabled' => $cancellation_enabled ?? $serviceProvider->cancellation_enabled ?? false,
            'cancellation_hours_before' => $cancellation_hours_before ?? $serviceProvider->cancellation_hours_before,
            'minimum_booking_lead_time_hours' => $minimum_booking_lead_time_hours ?? $serviceProvider->minimum_booking_lead_time_hours,
            'maximum_booking_lead_time_months' => $maximum_booking_lead_time_months ?? $serviceProvider->maximum_booking_lead_time_months,
        ]);

        return new ServiceProviderResource($serviceProvider);
    }

    /**
     * @throws \Exception
     */
    public function getServiceProviderDashboardInfo(ServiceProvider $serviceProvider, ?string $date_from, ?string $date_to, ?string $period): array
    {
        $count_of_confirmed_appointments = $this->countOfConfirmedAppointments($serviceProvider);
        $count_of_used_gift_cards = $this->countOfUsedGiftCards($serviceProvider);
        $count_of_cancelled_appointments = $this->countOfCancelledAppointments($serviceProvider);
        $count_of_bookings_per_day = $this->countOfBookingsPerDay($serviceProvider, $date_from, $date_to);
        $total_earnings = $this->getTotalEarnings($serviceProvider, $period);
        $most_booked_service = $this->getTheMostBookedService($serviceProvider);
        $most_preferred_service_type = $this->getTheMostPreferredServiceType($serviceProvider);

        return [
            'count_of_confirmed_appointments' => $count_of_confirmed_appointments,
            'count_of_used_gift_cards' => $count_of_used_gift_cards,
            'count_of_cancelled_appointments' => $count_of_cancelled_appointments,
            'count_of_bookings_per_day' => $count_of_bookings_per_day,
            'most_booked_service' => $most_booked_service,
            'most_preferred_service_type' => $most_preferred_service_type,
            'total_earnings' => $total_earnings,
        ];
    }

    public function countOfConfirmedAppointments(ServiceProvider $serviceProvider): ?int
    {
        return $serviceProvider
            ->appointments()
            ->where('status_id', AppointmentStatus::Confirmed->value)
            ->count();
    }

    public function countOfRejectedAppointments(ServiceProvider $serviceProvider): ?int
    {
        return $serviceProvider
            ->appointments()
            ->where('status_id', AppointmentStatus::Rejected->value)
            ->count();
    }

    public function countOfUsedGiftCards(ServiceProvider $serviceProvider)
    {
        return $serviceProvider->appointments()
            ->where(function ($query) {
                $query->whereNotNull('gift_card_id')
                    ->orWhereNotNull('promo_code_id')
                    ->orWhereNotNull('loyalty_discount_customer_id');
            })
            ->whereIn('status_id', [AppointmentStatus::Completed->value])
            ->count();
    }

    public function countOfCancelledAppointments(ServiceProvider $serviceProvider)
    {
        return $serviceProvider->appointments()
            ->where('status_id', AppointmentStatus::Cancelled->value)
            ->count();
    }

    public function countOfBookingsPerDay(ServiceProvider $serviceProvider, ?string $date_from, ?string $date_to)
    {
        $date_from = $date_from ? Carbon::parse($date_from)->format('Y-m-d') : Carbon::now()->format('Y-m-d');
        $date_to = $date_to ? Carbon::parse($date_to)->format('Y-m-d') : Carbon::parse($date_from)->addWeek()->format('Y-m-d');

        if ($date_from > $date_to) {
            throw new \Exception('date_from must be less than date_to');
        }

        $query = $serviceProvider->appointments()
            ->with('appointmentServices')
            ->where('status_id', AppointmentStatus::Confirmed->value)
            ->whereHas('appointmentServices', function ($query) use ($date_from, $date_to) {
                return $query->whereBetween('date', [$date_from, $date_to]);
            })
            ->get();

        $result = [];

        // Group appointments by date and time
        foreach ($query as $appointment) {
            foreach ($appointment->appointmentServices as $service) {
                $date = Carbon::parse($service->date)->format('d/m/Y');
                $time = Carbon::parse($service->start_time)->format('H:i');
                if (! isset($result[$date][$time])) {
                    $result[$date][$time] = [
                        'count' => 0,
                        'appointment_ids' => []
                    ];
                }
                $result[$date][$time]['count']++;
                if (!in_array($appointment->id, $result[$date][$time]['appointment_ids'])) {
                    $result[$date][$time]['appointment_ids'][] = $appointment->id;
                }
            }
        }

        // Prepare the response format
        $response = [];
        foreach ($result as $date => $appointments) {
            foreach ($appointments as $time => $data) {
                $response[] = [
                    'date' => $date,
                    'time' => $time,
                    'count' => $data['count'],
                    'appointment_ids' => $data['appointment_ids'],
                ];
            }
        }

        return $response;
    }

    public function getTotalEarnings(ServiceProvider $serviceProvider, ?string $period)
    {

        $date_from = Carbon::now()->startOfMonth()->format('Y-m-d');
        $date_to = Carbon::now()->endOfMonth()->format('Y-m-d');

        if ($period === 'w') {
            $date_from = Carbon::now()->startOfWeek()->format('Y-m-d');
            $date_to = Carbon::now()->endOfWeek()->format('Y-m-d');
        }

        if ($period === 'y') {
            $date_from = Carbon::now()->startOfYear()->format('Y-m-d');
            $date_to = Carbon::now()->endOfYear()->format('Y-m-d');
        }

        return $serviceProvider->appointments()
            ->where('status_id', AppointmentStatus::Completed->value)
            ->whereBetween('created_at', [$date_from, $date_to])
            ->with('services')
            ->get()
            ->pluck('services')
            ->flatten()
            ->map(function ($service) use ($serviceProvider) {
                return $serviceProvider->attachedServices->where('service_id', $service->id)->first()->price;
            })
            ->sum();

    }

    public function getTheMostBookedService(ServiceProvider $serviceProvider)
    {
        return $serviceProvider->appointments()
            ->where('status_id', AppointmentStatus::Completed->value)
            ->with('services')
            ->get()
            ->pluck('services')
            ->flatten()
            ->groupBy('id')
            ->map(function ($service) {
                return [
                    'service_name' => $service->first()->title,
                    'booking_count' => $service->count(),
                ];
            })
            ->sortByDesc('booking_count')
            ->take(5)
            ->values();
    }

    public function getTheMostPreferredServiceType(ServiceProvider $serviceProvider)
    {
        return $serviceProvider->appointments()
            ->where('status_id', AppointmentStatus::Completed->value)
            ->with(['appointmentServices' => function ($query) {
                return $query->with('deliveryType');
            }])
            ->get()
            ->pluck('appointmentServices')
            ->flatten()
            ->groupBy('deliveryType.title')
            ->values()
            ->map(function ($service) {
                return [
                    'service_type_name' => $service->first()->deliveryType->title,
                    'service_type_count' => $service->count(),
                ];
            })
            ->sortByDesc('service_type_count')
            ->take(2)
            ->values();

    }
}
