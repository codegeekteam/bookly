<?php

namespace App\Services;

use App\Http\Resources\AttachedServiceResource;
use App\Http\Resources\OperationalOffHoursResource;
use App\Http\Resources\ServiceResource;
use App\Models\AttachedService;
use App\Models\OperationalOffHour;
use App\Models\ServiceProvider;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class ServiceService
{
    public function index(?string $keyword, ?array $category_id, ?string $sort_direction)
    {
        $data = \App\Models\Service::with('categories');
        if ($category_id != null) {
            $data = $data->whereHas('categories', function ($query) use ($category_id) {
                $query->whereIn('category_id', $category_id);
            });
        }
        if ($keyword != null) {
            $data = $data->whereRaw('LOWER(title) LIKE ?', ['%' . strtolower($keyword) . '%']);
        }

        // Filter out services that are attached to the authenticated service provider
        if (auth('api')->check() && auth('api')->user()->serviceProvider) {

            $serviceProvider = auth('api')->user()->serviceProvider->id;

            $data = $data->whereDoesntHave('providers', function ($query) use ($serviceProvider) {
                $query->where('service_provider_id', $serviceProvider)->whereNull('attached_services.deleted_at'); // assuming 'provider_id' is the foreign key
            });
        }

        $data = $data
            ->where('is_active', true)
            ->orderBy('title', $sort_direction ?? 'asc')->with('providers')->get();

        return ServiceResource::collection($data);

    }

    public function getAttachedServiceById($attached_service_id)
    {
        $attachedService = AttachedService::find($attached_service_id);
        if (!$attachedService) {
            throw new \Exception(__('Service not found'));
        }

        return new AttachedServiceResource($attachedService);
    }

    public function getAttachedServicesForProviders(ServiceProvider $serviceProvider)
    {
        return AttachedServiceResource::collection($serviceProvider->attachedServices);
    }

    public function attachService(
        User $user,
        $service_id,
        ?float $price,
        ?array $delivery_types,
        ?string $description = null,
        array $ops_hours = [],
        int $has_deposit=0,
        ?float $deposit = 0,
    )
    {
        $serviceProvider = $user->serviceProvider;

        if ($serviceProvider->attachedServices()->where('service_id', $service_id)->whereNull('deleted_at')->exists()) {
            throw ValidationException::withMessages(['Service already attached']);
        }

        $serviceProvider->services()->attach($service_id, ['price' => $price, 'description' => $description, 'has_deposit' => $has_deposit, 'deposit' =>$has_deposit==0?0: $deposit]);

        $attachedService = $serviceProvider->attachedServices()->where('service_id', $service_id)->whereNull('deleted_at')->first();

        $attachedService ?->deliveryTypes()->attach(array_unique($delivery_types));
        $attachedService ?->operationalHours()->each(fn($ops_hour) => $ops_hour->delete());

        $days_of_week = [
            'SUN' => 'Sunday',
            'MON' => 'Monday',
            'TUE' => 'Tuesday',
            'WED' => 'Wednesday',
            'THU' => 'Thursday',
            'FRI' => 'Friday',
            'SAT' => 'Saturday',
        ];
        foreach ($ops_hours as $ops_hour) {
            $serviceProvider->operationalHours()->create([
                'service_id' => $service_id,
                'day_of_week' => $days_of_week[$ops_hour['day']],
                'start_time' => $ops_hour['start_time'],
                'end_time' => $ops_hour['end_time'],
                'duration_in_minutes' => $ops_hour['duration_in_minutes'],
            ]);
        }

        return new AttachedServiceResource($attachedService);

    }

    public function getAttachedServiceForCustomers(
        ?int $service_id,
        ?string $keyword,
        ?string $sort_direction,
        ?int $min_price,
        ?int $max_price,
        ?array $delivery_type_id,
        ?int $provider_id,
        bool $offers_filter = false,
        bool $nearest_appointment_filter = false,
        ?string $date_specific_search = null
    )
    {
        $data = \App\Models\AttachedService::with('service',
            'serviceProvider', 'deliveryTypes')
            ->whereHas('serviceProvider', function ($query) {
                $query->where('is_active', true)
                    ->where('is_blocked', false)
                    ->whereNull('deleted_at');
            });

        if ($service_id != null) {
            $data = $data->where('service_id', $service_id);
        }

        if ($keyword != null) {
            $data = $data->whereHas('serviceProvider', function ($query) use ($keyword) {
                $query->where('name', 'like', '%' . $keyword . '%')
                    ->whereNull('deleted_at');
            });
        }

        if ($provider_id != null) {
            $data = $data->where('service_provider_id', $provider_id);
        }

        if ($min_price != null || $max_price != null) {
            $data = $data->whereBetween('price', [$min_price ?? 0, $max_price ?? 999999999]);
        }

        if ($delivery_type_id != null) {
            $data = $data->whereHas('deliveryTypes', function ($query) use ($delivery_type_id) {
                $query->whereIn('delivery_type_id', $delivery_type_id);
            }, '=', count($delivery_type_id));
        }

        if ($offers_filter) {
            $data = $data->whereHas('service.promoCode', function ($query) {
                $query->where('start_date', '<=', Carbon::now())
                    ->where('end_date', '>=', Carbon::now())
                    ->where(function ($q) {
                        $q->whereNull('maximum_redeems')
                            ->orWhereColumn('count_of_redeems', '<', 'maximum_redeems');
                    });
            });
        }

        $data = $data->whereHas('service', function ($query) {
            $query->where('is_active', true);
        });

        if ($date_specific_search) {
            $searchDate = Carbon::parse($date_specific_search);
            $dayOfWeek = $searchDate->format('l');

            $data = $data->whereHas('serviceProvider.operationalHours', function ($query) use ($service_id, $dayOfWeek) {
                $query->where('service_id', $service_id)
                    ->where('day_of_week', $dayOfWeek);
            });
        }

        $data = $data->join('service_providers', 'attached_services.service_provider_id', '=', 'service_providers.id')
            ->select('attached_services.*');

        if (!$nearest_appointment_filter) {
            $data = $data->orderBy('service_providers.name', $sort_direction ?? 'asc');
        }

        $data = $data->get();

        if ($date_specific_search) {
            $data = $this->filterByDateAvailability($data, $service_id, $date_specific_search);
        }

        if ($nearest_appointment_filter) {
            $data = $this->sortByNearestAppointment($data, $service_id);
        }

        return AttachedServiceResource::collection($data)->additional(['nearest_appointment_filter' => $nearest_appointment_filter]);

    }

    public function updateAttachedService(
        User $user,
        $service_id,
        ?float $price,
        ?array $delivery_types,
        ?string $description,
        array $ops_hours = [],
        ?int $has_deposit,
        ?float $deposit,
    )
    {
        $serviceProvider = $user->serviceProvider;

        $attachedService = $serviceProvider->attachedServices()->where('service_id', $service_id)->whereNull('deleted_at')->first();

        if (!$attachedService) {
            throw ValidationException::withMessages(['Service not attached']);
        }
        if($has_deposit==0)
        {
            $deposit=0;
        }
        $attachedService->update([
            'price' => $price ?? $attachedService->price,
            'description' => $description ?? $attachedService->description,
            'has_deposit' => $has_deposit ?? $attachedService->has_deposit,
            'deposit' => $deposit ?? $attachedService->deposit,
        ]);

        if ($delivery_types != null) {
            $attachedService->deliveryTypes()->sync(array_unique($delivery_types));
        }

        $days_of_week = [
            'SUN' => 'Sunday',
            'MON' => 'Monday',
            'TUE' => 'Tuesday',
            'WED' => 'Wednesday',
            'THU' => 'Thursday',
            'FRI' => 'Friday',
            'SAT' => 'Saturday',
        ];

        $serviceProvider->operationalHours()->where('service_id', $service_id)->delete();

        foreach ($ops_hours as $ops_hour) {
            $serviceProvider->operationalHours()->create([
                'service_id' => $service_id,
                'day_of_week' => $days_of_week[$ops_hour['day']],
                'start_time' => $ops_hour['start_time'],
                'end_time' => $ops_hour['end_time'],
                'duration_in_minutes' => $ops_hour['duration_in_minutes'],
            ]);
        }

        return new AttachedServiceResource($attachedService);

    }

    public function delete(int $id)
    {
        $service = AttachedService::where('service_id', $id)
            ->where('service_provider_id', auth('sanctum')->user()->serviceProvider->id)->first();
        if (!$service) {
            return null;
        }

        $service->operationalHours()->each(fn($ops_hour) => $ops_hour->delete());

        return $service->delete();
    }

    public function addServiceOffHours(
        User $user,
        $service_id,
        array $off_hours = []
    )
    {

        $serviceProvider = $user->serviceProvider;

        $attachedService = $serviceProvider->attachedServices()->where('service_id', $service_id)->whereNull('deleted_at')->first();

        if (!$attachedService) {
            throw ValidationException::withMessages(['Service not attached']);
        }

        $days_of_week = [
            'SUN' => 'Sunday',
            'MON' => 'Monday',
            'TUE' => 'Tuesday',
            'WED' => 'Wednesday',
            'THU' => 'Thursday',
            'FRI' => 'Friday',
            'SAT' => 'Saturday',
        ];
        foreach ($off_hours as $off_hour) {
            $prev=$serviceProvider->operationalOffHours()
                ->where('service_id',$service_id)
                ->where('start_time',$off_hour['start_time'])
                ->where('end_time',$off_hour['end_time'])
                ->where('day_of_week',$days_of_week[$off_hour['day']])
                ->first();
            if(!$prev){
                $serviceProvider->operationalOffHours()->create([
                    'service_id' => $service_id,
                    'day_of_week' => $days_of_week[$off_hour['day']],
                    'start_time' => $off_hour['start_time'],
                    'end_time' => $off_hour['end_time'],
                ]);
            }
        }

        return response()->json(['message' => 'Service off hours added successfully'], 200);

    }

    public function getServiceOffHours(User $user, $request)
    {
        $serviceProvider = $user->serviceProvider;

        // Fetch all operational off hours
        $hours = $serviceProvider->operationalOffHours;

        // Check if a date is provided and filter by day of the week
        if ($request->has('date')) {
            $date = Carbon::parse($request->input('date'));  // Parse the date
            $dayOfWeek = $date->format('l');  // Get the full day of the week (e.g., 'Monday')

            // Filter the operationalOffHours by the day of the week
            $hours = $hours->where('day_of_week', $dayOfWeek);
        }

        return OperationalOffHoursResource::collection($hours);
    }

    public function deleteServiceOffHour(
        User $user,
        $off_hour_id
    )
    {

        $serviceProvider = $user->serviceProvider;

        $hour = $serviceProvider->operationalOffHours->where('id', $off_hour_id)->where('service_provider_id', $serviceProvider->id)->first();
        if (!$hour) {
            throw ValidationException::withMessages(['this hour not found']);
        }
        $hour->delete();
        return response()->json(['message' => 'this Service off hour deleted successfully'], 200);


    }

    public function updateServiceOffHour(
        User $user,
        $request
    )
    {

        $serviceProvider = $user->serviceProvider;

        $hour = $serviceProvider->operationalOffHours->where('id', $request->id)->where('service_provider_id', $serviceProvider->id)->first();
        if (!$hour) {
            throw ValidationException::withMessages(['this hour not found']);
        }
        $days_of_week = [
            'SUN' => 'Sunday',
            'MON' => 'Monday',
            'TUE' => 'Tuesday',
            'WED' => 'Wednesday',
            'THU' => 'Thursday',
            'FRI' => 'Friday',
            'SAT' => 'Saturday',
        ];
        $hour->day_of_week = $days_of_week[$request->day];
        $hour->start_time = $request->start_time;
        $hour->end_time = $request->end_time;
        $hour->save();
        return response()->json(['message' => 'this Service off hour updated successfully'], 200);


    }

    private function filterByDateAvailability($attachedServices, int $service_id, string $date)
    {
        $appointmentService = new AppointmentService();

        return $attachedServices->filter(function ($attachedService) use ($appointmentService, $service_id, $date) {
            try {
                $slots = $appointmentService->getAvailableSlots(
                    $attachedService->service_provider_id,
                    $service_id,
                    $date
                );

                return !empty($slots['slots']);
            } catch (\Exception $e) {
                return false;
            }
        })->values();
    }

    private function sortByNearestAppointment($attachedServices, int $service_id)
    {
        $appointmentService = new AppointmentService();
        $servicesWithSlots = [];

        foreach ($attachedServices as $attachedService) {
            $nearestSlot = $this->findNearestSlot($appointmentService, $attachedService->service_provider_id, $service_id);

            $attachedService->nearest_slot_info = $nearestSlot;
            $attachedService->nearest_slot_sort = $nearestSlot['sort_timestamp'] ?? PHP_INT_MAX;

            $servicesWithSlots[] = $attachedService;
        }

        usort($servicesWithSlots, function ($a, $b) {
            return $a->nearest_slot_sort <=> $b->nearest_slot_sort;
        });

        return collect($servicesWithSlots);
    }

    private function findNearestSlot(AppointmentService $appointmentService, int $provider_id, int $service_id): array
    {
        $today = Carbon::today();

        for ($i = 0; $i < 30; $i++) {
            $checkDate = $today->copy()->addDays($i);

            try {
                $slots = $appointmentService->getAvailableSlots(
                    $provider_id,
                    $service_id,
                    $checkDate->format('Y-m-d')
                );

                if (!empty($slots['slots'])) {
                    $firstSlot = $slots['slots'][0];
                    $slotTime = Carbon::parse($checkDate->format('Y-m-d') . ' ' . $firstSlot);

                    $dateLabel = $checkDate->isToday()
                        ? 'Today'
                        : $checkDate->format('d M');

                    $timeLabel = Carbon::parse($firstSlot)->format('h:i A');

                    return [
                        'label' => "{$dateLabel}, {$timeLabel}",
                        'date' => $checkDate->format('Y-m-d'),
                        'time' => $firstSlot,
                        'sort_timestamp' => $slotTime->timestamp
                    ];
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        return [
            'label' => 'No available slots soon',
            'date' => null,
            'time' => null,
            'sort_timestamp' => PHP_INT_MAX
        ];
    }
}
