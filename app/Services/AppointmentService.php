<?php

namespace App\Services;

use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Customer;
use App\Models\GiftCard;
use App\Models\PromoCode;
use App\Models\Appointment;
use App\Models\HeldTimeSlot;
use App\Models\Subscription;
use Illuminate\Http\Request;
use App\Models\PaymentMethod;
use App\Helpers\PayfortHelper;
use App\Models\AttachedService;
use App\Models\ServiceProvider;
use App\Enums\AppointmentStatus;
use App\Services\InvoiceService;
use App\Settings\RewardsSettings;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Enums\TransactionType;
use App\Http\Resources\AppointmentResource;
use App\Http\Resources\AppointmentCollection;
use App\Notifications\AppointmentNotification;
use Illuminate\Validation\ValidationException;
use App\Notifications\NewAppointmentNotification;
use App\Notifications\RequestPaymentNotification;
use App\Notifications\AppointmentCompleteNotification;
use App\Actions\PromoCode\Mutations\CheckPromoCodeMutation;
use App\Notifications\AcceptRescheduleAppointmentNotification;
use App\Notifications\RejectRescheduleAppointmentNotification;
use App\Actions\Wallet\Mutations\CreateWalletTransactionMutation;
use App\Actions\PromoCode\Mutations\PromoCodeCalculationsMutation;
use App\Actions\LoyaltyPoints\Mutations\CreatePointTransactionMutation;
use App\Actions\LoyaltyPoints\Mutations\CheckLoyaltyDiscountUsageMutation;
use App\Actions\LoyaltyPoints\Mutations\LoyaltyDiscountCalculationsMutation;

class AppointmentService
{
    /**
     * @throws Exception
     */
    public function getAvailableSlots(int $provider_id, int $service_id, ?string $date = null, ?int $employee_id = null): array
    {
        // 1. Fetch provider and service details, or throw an exception if not found
        $provider = ServiceProvider::with('operationalHours', 'operationalOffHours')->find($provider_id);
        $service = \App\Models\Service::find($service_id);


        if (!$provider) {
            throw new Exception(__('Provider not found'));
        }

        if (!$service) {
            throw new Exception(__('Provider does not provide this service'));
        }

        // Validate employee if provided (for enterprise providers)
        if ($employee_id) {
            $employee = \App\Models\Employee::where('id', $employee_id)
                ->where('provider_id', $provider_id)
                ->whereHas('services', function ($query) use ($service_id) {
                    $query->where('services.id', $service_id);
                })
                ->first();

            if (!$employee) {
                throw new Exception(__('Employee not found or cannot perform this service'));
            }
        }

        // 2. Set the date or default to today, ensuring it's not in the past
        $date = $date ? Carbon::parse($date)->format('Y-m-d') : Carbon::today()->format('Y-m-d');
        if ($date < Carbon::now()->format('Y-m-d')) {
            throw new Exception('Date is in the past');
        }

        $day_of_date = Carbon::parse($date)->format('l');  // Use Carbon for day parsing

        // 3. Fetch operational hours for the provider on the given day
        $ops_hours = $provider->operationalHours()
            ->where('service_id', $service_id)
            ->where('day_of_week', $day_of_date)
            ->first();

        if (!$ops_hours) {
            return ['slots' => []];  // No operational hours means no available slots
        }

        // 4. Fetch off hours for the provider on the given day
        $off_hours = $provider->operationalOffHours()
            ->where('service_id', $service_id)
            ->where('day_of_week', $day_of_date)
            ->get(['start_time', 'end_time']);

        // 5. Generate slots based on operational hours and duration
        $duration = $ops_hours->duration_in_minutes;
        $slots = [];
        $start_time = Carbon::parse($ops_hours->start_time);
        $end_time = Carbon::parse($ops_hours->end_time);

        // Loop through each time slot based on duration
        while ($start_time->lessThan($end_time)) {
            $is_in_off_hours = $off_hours->some(function ($off_hour) use ($start_time) {
                $off_start = Carbon::parse($off_hour->start_time);
                $off_end = Carbon::parse($off_hour->end_time);
                return $start_time->between($off_start, $off_end);
            });

            if (!$is_in_off_hours) {
                $slots[] = $start_time->format('H:i');
            }

            $start_time->addMinutes($duration);  // Move to the next time slot
        }

        // 6. Fetch booked appointments for the provider on the given date
        $booked_appointments = \App\Models\AppointmentService::where('date', $date)
            ->whereHas('appointment', function ($query) use ($provider) {
                $query->where('service_provider_id', $provider->id)
                    ->whereIn('status_id', [1, 2, 6]);  // Only include certain statuses
            })
            ->when($employee_id, function ($query) use ($employee_id) {
                // For enterprise providers, filter by employee_id
                $query->where('employee_id', $employee_id);
            })
            ->get(['start_time', 'end_time']);  // Fetch only relevant fields

        // 7. Remove slots that overlap with booked appointments
        if ($booked_appointments->isNotEmpty()) {
            $slots = array_filter($slots, function ($slot) use ($booked_appointments, $duration) {
                $slot_start = Carbon::parse($slot);
                $slot_end = $slot_start->copy()->addMinutes($duration);

                // Check if the slot overlaps with any booked appointment
                foreach ($booked_appointments as $appointment) {
                    $booked_start = Carbon::parse($appointment->start_time);
                    $booked_end = Carbon::parse($appointment->end_time);

                    if (
                        $slot_start->between($booked_start, $booked_end) ||
                        $slot_end->between($booked_start, $booked_end) ||
                        ($slot_start <= $booked_start && $slot_end >= $booked_end)
                    ) {
                        return false;
                    }
                }

                return true;  // Keep the slot if no overlap is found
            });
        }

        // 8. Fetch held time slots and remove them from available slots
        $held_time_slots = $service->heldTimeSlots()
            ->where('service_provider_id', $provider_id)
            ->whereDate('date', $date)
            ->where('expires_at', '>', Carbon::now())
            ->pluck('timeSlot')
            ->map(fn($timeSlot) => Carbon::parse($timeSlot)->format('H:i'))
            ->toArray();

        // Remove held time slots from the available slots
        $slots = array_values(array_diff($slots, $held_time_slots));

        // 9. Apply minimum booking lead time filter
        if ($provider->minimum_booking_lead_time_hours !== null) {
            $minimumBookingTime = Carbon::now()->addHours($provider->minimum_booking_lead_time_hours);
            $slots = array_filter($slots, function ($slot) use ($date, $minimumBookingTime) {
                $slotDateTime = Carbon::parse($date . ' ' . $slot);
                return $slotDateTime->greaterThanOrEqualTo($minimumBookingTime);
            });
            $slots = array_values($slots);
        }

        // 10. Apply maximum booking lead time filter
        if ($provider->maximum_booking_lead_time_months !== null) {
            $maximumBookingTime = Carbon::now()->addMonths($provider->maximum_booking_lead_time_months);
            $slots = array_filter($slots, function ($slot) use ($date, $maximumBookingTime) {
                $slotDateTime = Carbon::parse($date . ' ' . $slot);
                return $slotDateTime->lessThanOrEqualTo($maximumBookingTime);
            });
            $slots = array_values($slots);
        }

        // 11. Format remaining slots to a readable format (e.g., 01:00 pm)
        $slots = array_map(fn($slot) => Carbon::parse($slot)->format('h:i a'), $slots);

        // 12. Return the final list of available slots
        return ['slots' => $slots];
    }


    public function holdTimeSlot(
        int $provider_id,
        int $service_id,
        string $date,
        string $time,
    ) {

        $provider = ServiceProvider::find($provider_id);

        $service = \App\Models\Service::find($service_id);

        if (!$provider) {
            throw new Exception(__('Provider not found'));
        }

        if (!$service) {
            throw new Exception(__('Provider does not provide this service'));
        }

        $date = Carbon::parse($date)->format('Y-m-d');
        $day_of_date = date('l', strtotime($date));

        $ops_hours = $provider->operationalHours()
            ->where('service_id', $service_id)
            ->where('day_of_week', $day_of_date)
            ->first();

        if (!$ops_hours) {
            throw new Exception(__('Provider is not available on this day'));
        }

        $duration = $ops_hours->duration_in_minutes;

        $time_from = Carbon::parse($time)->format('H:i:s');
        $time_to = Carbon::parse($time)->addMinutes($duration)->format('H:i:s');

        if ($ops_hours->start_time > $time_from || $ops_hours->end_time < $time_to) {
            throw new Exception(__('Provider is not available on this time'));
        }

        $booked_appointments = $provider->appointments()
            ->whereHas('services', function ($query) use ($service_id, $time_from, $date) {
                $query->where('service_id', $service_id)
                    ->where('start_time', $time_from)
                    ->where('date', $date);
            })->first();

        if ($booked_appointments) {
            throw new Exception(__('Time slot is not available'));
        }

        $heldTimeSlot = HeldTimeSlot::create([
            'service_id' => $service_id,
            'date' => $date,
            'service_provider_id' => $provider_id,
            'expires_at' => Carbon::now()->addMinutes(5),
            'timeSlot' => Carbon::parse($time),
        ]);

        return $heldTimeSlot;
    }

    public
    function get(
        User $user
    ) {
        if ($user->serviceProvider) {
            $appointments = $user->serviceProvider->appointments;
            if ($appointments->count() > 0) {
                return new AppointmentCollection($appointments->load('serviceProvider', 'services', 'appointmentServices', 'customer', 'PromoCode', 'paymentMethod', 'invoice')->sortByDesc('id'));
            }

            return response()->json([]);
        }
        if ($user->customer) {
            $appointments = $user->customer->appointments;
            if ($appointments->count() > 0) {
                return new AppointmentCollection($appointments->load('serviceProvider', 'services', 'appointmentServices', 'customer', 'PromoCode', 'paymentMethod', 'invoice')->sortByDesc('id'));
            }

            return response()->json([]);
        }
        throw new Exception(__('User not found'));
    }

    public
    function getById(
        User $user,
        $request
    ) {
        if ($user->serviceProvider) {
            $appointment = $user->serviceProvider->appointments->where('id', $request->id)->first();
            if ($appointment) {
                return AppointmentResource::make($appointment->load('serviceProvider', 'services', 'appointmentServices', 'customer', 'PromoCode', 'paymentMethod', 'invoice'));
            }
        }
        if ($user->customer) {
            $appointment = $user->customer->appointments->where('id', $request->id)->first();
            if ($appointment) {
                return AppointmentResource::make($appointment->load('serviceProvider', 'services', 'appointmentServices', 'customer', 'PromoCode', 'paymentMethod', 'invoice'));
            }
        }
        throw new Exception(__('Appointment not found'));
    }

    public
    function holdAppointment(
        Request $request
    ) {
        $appointment = Appointment::find($request->appointment_id);

        if (!$appointment) {
            throw new Exception(__('Appointment not found'));
        }

        $appointment->update([
            'status_id' => 2,
        ]);

        return AppointmentResource::make($appointment);
    }

    /**
     * @throws Exception
     */
    public function book(
        Customer $customer,
        array $services,
        ?string $promo_code,
        ?string $comment,
        ?int $payment_method_id,
        ?int $loyalty_discount_customer_id
    ) {
        $loyalty_discount = null;
        $serviceProviderId = array_reduce($services, static function ($carry, $service) {
            return $carry === $service['provider_id'] ? $carry : false;
        }, $services[0]['provider_id']);

        if (!$serviceProviderId) {
            throw ValidationException::withMessages([
                'services.0.provider_id' => __('All services must be from the same provider'),
            ]);
        }

        $provider = ServiceProvider::findOrFail($serviceProviderId);

        // Validate booking lead times
        foreach ($services as $service) {
            $dateOnly = Carbon::parse($service['date'])->format('Y-m-d');
            $bookingDateTime = Carbon::parse($dateOnly . ' ' . $service['time_slot']);

            // Check minimum booking lead time
            if ($provider->minimum_booking_lead_time_hours !== null) {
                $minimumBookingTime = Carbon::now()->addHours($provider->minimum_booking_lead_time_hours);
                if ($bookingDateTime->lessThan($minimumBookingTime)) {
                    throw ValidationException::withMessages([
                        'services' => __('Booking must be made at least :hours hours in advance', [
                            'hours' => $provider->minimum_booking_lead_time_hours
                        ]),
                    ]);
                }
            }

            // Check maximum booking lead time
            if ($provider->maximum_booking_lead_time_months !== null) {
                $maximumBookingTime = Carbon::now()->addMonths($provider->maximum_booking_lead_time_months);
                if ($bookingDateTime->greaterThan($maximumBookingTime)) {
                    throw ValidationException::withMessages([
                        'services' => __('Booking cannot be made more than :months months in advance', [
                            'months' => $provider->maximum_booking_lead_time_months
                        ]),
                    ]);
                }
            }
        }

        foreach ($services as $i => $service) {
            $dateOnly = Carbon::parse($service['date'])->format('Y-m-d');
            $duration = $provider->operationalHours()
                ->where('day_of_week', Carbon::parse($service['date'])->format('l'))
                ->where('service_id', $service['service_id'])
                ->first()->duration_in_minutes;

            $services[$i]['start_time'] = Carbon::parse($dateOnly . ' ' . $service['time_slot']);
            $services[$i]['end_time'] = Carbon::parse($dateOnly . ' ' . $service['time_slot'])->addMinutes($duration);
        }
        if ($promo_code && $loyalty_discount_customer_id) {
            throw ValidationException::withMessages([
                'promo_code' => __('You can only use either a promo code or a loyalty discount, not both.'),
            ]);
        }
        //check promo code
        if ($promo_code) {
            (new CheckPromoCodeMutation())->handle($promo_code, $customer);
            $promo_code = PromoCode::where('code', $promo_code)->first();
        }

        //check loyalty discount
        if ($loyalty_discount_customer_id) {
            $loyalty_discount = (new CheckLoyaltyDiscountUsageMutation())->handle($loyalty_discount_customer_id, $customer);
        }


        //comment it until testing finished
        /*$is_daily_limit_reached = !($provider->max_appointments_per_day == null) && $provider->appointments()
                ->where('created_at', '>=', Carbon::now()->startOfDay())
                ->where('created_at', '<=', Carbon::now()->endOfDay())
                ->count() >= $provider->max_appointments_per_day;

        if ($is_daily_limit_reached) {
            throw ValidationException::withMessages([
                'services' => 'Daily limit reached',
            ]);
        }*/


        $sum_of_services = 0;
        $amount_due = 0;
        $total_deposit_amount = 0;
        $has_any_deposit = false;

        //begin db transaction
        DB::beginTransaction();

        // Get Card payment method ID for deposits
        $cardPaymentMethod = PaymentMethod::where('name', 'Card')->where('is_active', true)->first();

        //create the appointment
        $appointment = Appointment::create([
            'service_provider_id' => $serviceProviderId,
            'customer_id' => $customer->id,
            'payment_method_id' => $payment_method_id,
            'comment' => $comment,
            'status_id' => AppointmentStatus::Pending->value,
            'promo_code_id' => $promo_code ? $promo_code->id : null,
            'loyalty_discount_customer_id' => $loyalty_discount ? $loyalty_discount->id : null,
        ]);



        //save services and calculate the total
        foreach ($services as $service) {
            $appointment->services()->attach($service['service_id'], [
                'date' => Carbon::parse($service['date'])->format('Y-m-d'),
                'start_time' => $service['start_time']->format('H:i:s'),
                'end_time' => $service['end_time']->format('H:i:s'),
                'number_of_beneficiaries' => $service['number_of_beneficiaries'],
                'delivery_type_id' => $service['delivery_type_id'],
                'address_id' => isset($service['address_id']) ? $service['address_id'] : null,
                'employee_id' => isset($service['employee_id']) ? $service['employee_id'] : null,
            ]);
            // Calculate the price for this service and add to total
            $attachedService = AttachedService::where('service_provider_id', $provider->id)
                ->where('service_id', $service['service_id'])
                ->first();

            if ($attachedService) {
                $price = $attachedService->price;
                $beneficiaries = $service['number_of_beneficiaries'];

                // always add to total
                $sum_of_services += $price * $beneficiaries;

                // deposit logic
                if ($attachedService->has_deposit) {
                    $has_any_deposit = true;
                    $depositPrice = ($attachedService->deposit / 100) * $price;
                    $total_deposit_amount += $depositPrice * $beneficiaries;
                    $amount_due += $depositPrice * $beneficiaries;
                } else {
                    $amount_due += $price * $beneficiaries;
                }
            }
        }

        $discount = 0;
        //calculate promo code
        if ($promo_code) {
            $discount = (new PromoCodeCalculationsMutation())->handle($promo_code, $sum_of_services);
            $promo_code->increment('count_of_redeems');
        }

        //calculate loyalty discount
        if ($loyalty_discount) {
            if ($sum_of_services < $loyalty_discount->minimum_amount) {
                throw new \Exception(__("this discount not applicable , minimum amount to use this discount is") . " $loyalty_discount->minimum_amount ]");
            }
            $discount = (new LoyaltyDiscountCalculationsMutation())->handle($loyalty_discount, $sum_of_services);
            $loyalty_discount->update(['is_used' => 1]);
        }

        $appointment->total = $sum_of_services - $discount;
        $appointment->amount_due = max(0, $amount_due - $discount);
        $appointment->discount = $discount;

        // Handle deposit and remaining payment tracking
        if ($has_any_deposit && $total_deposit_amount > 0) {
            // Apply discount proportionally
            $deposit_after_discount = max(0, $total_deposit_amount - ($discount * ($total_deposit_amount / $sum_of_services)));
            $remaining_after_discount = max(0, ($sum_of_services - $total_deposit_amount) - ($discount * (($sum_of_services - $total_deposit_amount) / $sum_of_services)));

            $appointment->deposit_amount = $deposit_after_discount;
            $appointment->deposit_payment_status = $deposit_after_discount > 0 ? 'pending' : 'paid';
            $appointment->deposit_payment_method_id = $cardPaymentMethod ? $cardPaymentMethod->id : null;

            $appointment->remaining_amount = $remaining_after_discount;
            $appointment->remaining_payment_status = $remaining_after_discount > 0 ? 'pending' : 'paid';
            $appointment->remaining_payment_method_id = $payment_method_id;

            // Set overall payment status
            if ($appointment->amount_due == 0) {
                $appointment->payment_status = 'paid';
            } else if ($deposit_after_discount == 0 || $appointment->deposit_payment_status == 'paid') {
                $appointment->payment_status = 'partially_paid';
            } else {
                $appointment->payment_status = 'unpaid';
            }
        } else {
            // No deposit required - full amount payment
            $appointment->deposit_amount = null;
            $appointment->deposit_payment_status = null;
            $appointment->deposit_payment_method_id = null;

            $appointment->remaining_amount = $appointment->amount_due;
            $appointment->remaining_payment_status = $appointment->amount_due > 0 ? 'pending' : 'paid';
            $appointment->remaining_payment_method_id = $payment_method_id;

            $appointment->payment_status = $appointment->amount_due == 0 ? 'paid' : 'unpaid';
        }

        $appointment->save();


        // 🔹 Auto complete appointment if Card payment

        if ($payment_method_id) {
            $paymentMethod = PaymentMethod::find($payment_method_id);

            if (($paymentMethod && strtolower($paymentMethod->name) == 'card') && ($appointment->payment_status == 'paid')) {
                $appointment->state()->confirm();
                $this->markAsComplete($appointment);
            }

            // 🔹 Notify provider to mark booking complete if Cash payment
            else if ($paymentMethod && strtolower($paymentMethod->name) === 'cash') {
                try {
                    $appointment->serviceProvider->user
                        ->notify(new AppointmentCompleteNotification($appointment));
                } catch (Exception $e) {
                    Log::error('Failed to send AppointmentCompleteNotification', [
                        'appointment_id'      => $appointment->id,
                        'provider_user_id'     => $appointment->serviceProvider->user_id ?? null,
                        'payment_method_id'   => $payment_method_id,
                        'payment_method_name' => $paymentMethod->name ?? null,
                        'exception'            => $e->getMessage(),
                        'trace'                => $e->getTraceAsString(),
                    ]);
                }
            }
        }

        //customer wallet check
        $this->customerWalletActions($customer, $appointment);

        //add total to provider wallet
        (new CreateWalletTransactionMutation())
            ->handle(
                $provider->user->wallet,
                $appointment->amount_due,
                TransactionType::IN,
                "Appointment #$appointment->id booking",
                false,
                " حجز موعد رقم : $appointment->id"
            );
        //commit changes
        DB::commit();

        //clear cart
        (new CartService())->clearCart($customer);

        //send notification if no deposit required
        if ($appointment->deposit_amount === null) {
            try { 
                $appointment->serviceProvider->user->notify(new NewAppointmentNotification($appointment)); 
           } catch (Exception $e) {
                Log::info($e);
            } 
        }

        return new AppointmentResource($appointment);
    }

    public function customerWalletActions($customer, $appointment)
    {
        //check customer wallet
        $wallet = $customer->user->wallet;
        if ($wallet) {
            if ($wallet->balance > 0) {
                $payed_amount = 0;
                //the balance cover the total
                if ($wallet->balance >= $appointment->amount_due) {
                    $appointment->wallet_amount = $appointment->amount_due;
                    $appointment->payment_status = 'paid';
                    $appointment->total_payed = $appointment->amount_due;
                    $appointment->payment_method_id = 2; //wallet
                    $appointment->save();
                    $payed_amount = $appointment->wallet_amount;
                }
                //total greater than balance
                else {
                    $appointment->wallet_amount = $wallet->balance;
                    $appointment->payment_status = 'partially_paid';
                    $appointment->total_payed = $wallet->balance;
                    $appointment->payment_method_id = 3; //wallet and card
                    $appointment->save();
                    $payed_amount = $appointment->wallet_amount;
                }

                //add wallet transaction
                (new CreateWalletTransactionMutation())
                    ->handle(
                        $wallet,
                        $payed_amount,
                        TransactionType::OUT,
                        "Appointment #$appointment->id booking using your wallet",
                        false,
                        " حجز موعد رقم : $appointment->id"
                    );
            }
        }
    }
    /**
     * @throws Exception
     */
    public function reschedule(
        $provider,
        int $appointment_id,
        int $service_id,
        ?int $employee_id,
        string $date,
        string $timeslot
    ): AppointmentResource {

        $rescheduleDate = Carbon::parse($date);
        $rescheduleTime = Carbon::parse($timeslot);
        $currentDateTime = Carbon::now();


        $appointment = Appointment::find($appointment_id);

        if (!$appointment) {
            throw new Exception(__('Appointment not found'));
        }

        Log::critical('customer_id ' . $provider->id);

        if ($appointment->service_provider_id != $provider->id) {
            throw new Exception(__('Appointment not found'));
        }

        if ($appointment->status_id != AppointmentStatus::Confirmed->value && $appointment->status_id != AppointmentStatus::Pending->value) {
            throw new Exception(__('Appointment is not in confirmed or pending status'));
        }
        //check if appointment have one service or many
        $serviceCount = $appointment->services()->count();
        if ($serviceCount != 1) {
            throw new Exception(__('cant reschedule this appointment because has many services not only one'));
        }

        $booked_service = $appointment->services()->where('service_id', $service_id)->first();

        Log::critical('booked_service ' . $booked_service->service_id);

        if (!$booked_service) {
            throw new Exception(__('Service not found'));
        }

        if ($rescheduleDate->clone()->isBefore($currentDateTime->clone()->startOfDay())) {
            throw new Exception(__('You cannot reschedule to a past date.'));
        }


        if ($rescheduleDate->isSameDay($currentDateTime) && $rescheduleTime->isBefore($currentDateTime)) {
            throw new Exception(__('The rescheduled time cannot be in the past.'));
        }


        $date_in_ops_hours = $booked_service->operationalHours()->where(
            'day_of_week',
            $rescheduleDate->format('l')
        )->exists();

        if (!$date_in_ops_hours) {
            throw new Exception(__('The selected date is not available for this service.'));
        }

        $is_time_slot_same_time = $rescheduleTime->eq(Carbon::parse($booked_service->start_time));

        if ($is_time_slot_same_time) {
            throw new Exception(__('The rescheduled time slot is the same as the original time.'));
        }

        $available_timeslots = $this->getAvailableSlots(
            $appointment->service_provider_id,
            $booked_service->id,
            $rescheduleDate
        )['slots'];

        if (!in_array($timeslot, $available_timeslots)) {
            throw new Exception(__('The selected time slot is not available.'));
        }

        $duration = $appointment->serviceProvider->operationalHours()
            ->where('day_of_week', $rescheduleDate->format('l'))
            ->where('service_id', $booked_service->id)
            ->first()->duration_in_minutes;



        $appointment->state()->rescheduleRequest();

        $booked_service->pivot->update([
            'new_start_time' => $rescheduleTime,
            'new_end_time' => $rescheduleTime->copy()->addMinutes($duration),
            'new_date' => $rescheduleDate->format('Y-m-d')
        ]);

        try {
            $title = 'Appointment Reschedule Request';
            $body = 'Your appointment #' . $appointment->id . ' has been requested to be rescheduled to ' . $rescheduleTime->format('H:i') . ' on ' . $rescheduleDate->format('Y-m-d');
            $title_ar = 'طلب تعديل موعد';
            $body_ar = 'تم طلب تعديل موعد رقم :  ' . $appointment->id . 'الى : ' . $rescheduleTime->format('H:i') . '  ' . $rescheduleDate->format('Y-m-d');
            $appointment->customer->user->notify(new AppointmentNotification($title, $body, $title_ar, $body_ar));
        } catch (\Exception $e) {
            Log::info($e);
        }

        return new AppointmentResource($appointment);
    }


    /**
     * @throws Exception
     */
    public
    function customerRescheduleResponse(
        int $appointment_id,
        int $service_id,
        int $customer_id,
        string $customer_response
    ): JsonResponse {
        $appointment = Appointment::find($appointment_id);

        if (!$appointment) {
            throw new Exception(__('Appointment not found'));
        }

        if ($appointment->customer_id != $customer_id) {
            throw new Exception(__('Appointment not found'));
        }

        if ($appointment->status_id != AppointmentStatus::RescheduleRequest->value) {
            throw new Exception(__('Appointment is not in reschedule request status'));
        }

        if ($customer_response === 'accept') {
            $appointment->services()->where('service_id', $service_id)->update([
                'start_time' => $appointment->services()->where(
                    'service_id',
                    $service_id
                )->first()->pivot->new_start_time,
                'end_time' => $appointment->services()->where('service_id', $service_id)->first()->pivot->new_end_time,
                'date' => $appointment->services()->where('service_id', $service_id)->first()->pivot->new_date,
                'new_start_time' => null,
                'new_end_time' => null,
                'new_date' => null,
                'accepted_reschedule' => true,
            ]);

            $appointment->update([
                'status_id' => $appointment->previous_status_id,
            ]);
            //notification
            try {
                $appointment->serviceProvider->user->notify(new AcceptRescheduleAppointmentNotification($appointment));
            } catch (\Exception $e) {
                Log::info($e);
            }
            return response()->json([
                'message' => __('appointment rescheduled successfully'),
            ], 200);
        } elseif ($customer_response === 'reject') {
            $appointment->services()->where('service_id', $service_id)->update([
                'new_start_time' => null,
                'new_end_time' => null,
                'new_date' => null,
                'accepted_reschedule' => false,
            ]);

            $appointment->update([
                'status_id' => $appointment->previous_status_id,
            ]);

            //notification
            try {
                $appointment->serviceProvider->user->notify(new RejectRescheduleAppointmentNotification($appointment));
            } catch (\Exception $e) {
                Log::info($e);
            }
            return response()->json([
                'message' => __('appointment reschedule rejected the appointment is still in the original time'),
            ], 200);
        } else {
            throw new Exception(__('Invalid response'));
        }
    }

    public
    function getSDKToken($device_id) {
        $payfort_helper = new PayfortHelper();

        $payment_gateway_response = $payfort_helper->generateSDKToken($device_id);

        \Log::info('response',  ['response' =>$payment_gateway_response]);
              

        if ($payment_gateway_response['response_code'] !== '22000') {

            throw ValidationException::withMessages(['message' => $payment_gateway_response['response_message']]);
        }

        return [
            'payment' => [
                'sdk_token' => $payment_gateway_response['sdk_token'],
                'response_message' => $payment_gateway_response['response_message'],
                'response_code' => $payment_gateway_response['response_code'],
            ],

        ];
  
    }

    /* public
    function getPayfortFeedback(
        $response_code,
        $appointment_id,
        $amount
    ) {
        $appointment = Appointment::find($appointment_id);

        if (!$appointment) {
            response()->noContent();
        }

        if (substr($response_code, 2) != '000') {
            response()->noContent();
        }

        if ($amount+$appointment->total_payed >=$appointment->total) {
            $appointment->update([
                'payment_status' => 'paid',
                'card_amount'=>$amount,
                'total_payed'=>$amount+$appointment->total_payed
            ]);
        } else {
            $appointment->update([
                'payment_status' => 'partially_paid',
                'card_amount'=>$amount,
                'total_payed'=>$amount+$appointment->total_payed
            ]);
        }

        return response()->json([
            'message' => 'success',
        ], 200);
    }*/

    public function getPayfortFeedback($response_code, $id, $amount)
    {
        // normalize amount (Payfort sends multiplied by 100)
        $normalizedAmount = $amount / 100;

        // Split the ID into type and identifier
        $parts = explode('_', $id);

        if (count($parts) < 2) {
            return response()->json(['message' => 'Invalid ID format'], 200);
        }

        // Handle different merchant reference formats:
        // - appointment_203 (original format - 2 parts)
        // - appointment_203_1698765432000 (new format with timestamp - 3 parts)
        // - remaining_payment_203_1698765432000 (alternative format - 4 parts)

        $type = null;
        $identifier = null;
        $paymentType = 'auto'; // auto-detect by default

        if ($parts[0] === 'remaining' && count($parts) >= 3 && $parts[1] === 'payment') {
            // Format: remaining_payment_203 or remaining_payment_203_timestamp
            $type = 'appointment';
            $identifier = $parts[2];
            $paymentType = 'remaining';
        } elseif ($parts[0] === 'deposit' && count($parts) >= 3 && $parts[1] === 'payment') {
            // Format: deposit_payment_203 or deposit_payment_203_timestamp
            $type = 'appointment';
            $identifier = $parts[2];
            $paymentType = 'deposit';
        } else {
            // Format: appointment_203 or appointment_203_timestamp
            // Format: giftCard_123 or giftCard_123_timestamp
            // Format: subscription_456 or subscription_456_timestamp
            $type = $parts[0];
            $identifier = $parts[1];
        }

        // Resolve model
        $model = match ($type) {
            'appointment' => Appointment::find($identifier),
            'giftCard'    => GiftCard::find($identifier),
            'subscription' => Subscription::find($identifier),
            default       => null,
        };

        if (!$model) {
            return response()->json(['message' => 'Resource not found'], 200);
        }

        // Check success response_code
        if (substr($response_code, 2) !== '000') {
            return response()->json(['message' => 'Invalid response code'], 200);
        }

        // Appointment logic with deposit/remaining tracking
        if ($type === 'appointment') {
            $appointment = $model;

            // Determine payment type based on explicit type or auto-detect
            $isDepositPayment = false;
            $isRemainingPayment = false;

            if ($paymentType === 'deposit') {
                $isDepositPayment = true;
            } elseif ($paymentType === 'remaining') {
                $isRemainingPayment = true;
            } else {
                // Auto-detect based on appointment status
                if ($appointment->deposit_amount && $appointment->deposit_payment_status !== 'paid') {
                    $isDepositPayment = true;
                } else {
                    $isRemainingPayment = true;
                }
            }

            // Process deposit payment
            if ($isDepositPayment && $appointment->deposit_amount) {
                if ($normalizedAmount >= $appointment->deposit_amount) {
                    $appointment->deposit_payment_status = 'paid';
                    $appointment->card_amount = ($appointment->card_amount ?? 0) + $normalizedAmount;
                    $appointment->total_payed = ($appointment->total_payed ?? 0) + $normalizedAmount;

                    // Update overall status
                    if ($appointment->remaining_amount == 0 || $appointment->remaining_amount == null) {
                        $appointment->payment_status = 'paid';
                    } else {
                        $appointment->payment_status = 'partially_paid';
                    }
                }
            }
            // Process remaining payment or full payment
            else {
                $newTotal = $normalizedAmount + ($appointment->total_payed ?? 0);
                $isPaid = $newTotal >= $appointment->amount_due;

                if ($appointment->remaining_amount) {
                    $appointment->remaining_payment_status = $isPaid ? 'paid' : 'pending';
                }

                $appointment->payment_status = $isPaid ? 'paid' : 'partially_paid';
                $appointment->card_amount = ($appointment->card_amount ?? 0) + $normalizedAmount;
                $appointment->total_payed = $newTotal;
            }

            $appointment->save();

            // Generate invoice if payment is complete
            if ($appointment->payment_status === 'paid' && !$appointment->invoice) {
                try {
                    $invoiceService = new InvoiceService();
                    $invoiceService->generateInvoice($appointment);
                } catch (\Exception $e) {
                    \Log::error('Failed to generate invoice for appointment ' . $appointment->id . ': ' . $e->getMessage());
                }
            }
            \Log::info('isDepositPayment: ', $isDepositPayment);
            \Log::info('deposit_payment_status: ', $appointment->deposit_payment_status);

            // Send notification to provider when deposit is paid
            if ($isDepositPayment && $appointment->deposit_payment_status === 'paid') {
                try {
                    $appointment->serviceProvider->user->notify(new NewAppointmentNotification($appointment));
                } catch (\Exception $e) {
                    Log::info($e);
                }
            }
        }

        // GiftCard logic
        if ($type === 'giftCard') {
            $model->update([
                'payment_status' => 'paid',
            ]);
        }

        // Subscription logic
        if ($type === 'subscription') {
            $model->update([
                'payment_status' => 'paid',
            ]);
        }

        return response()->json(['message' => 'success'], 200);
    }

    public function getAvailableDates(int $provider_id, int $service_id, ?string $date = null): array
    {
        // Fetch provider and service details
        $provider = ServiceProvider::with('operationalHours', 'operationalOffHours')->find($provider_id);
        $service = \App\Models\Service::with('heldTimeSlots')->find($service_id);

        if (!$provider || !$service) {
            throw new Exception(__('Provider or service not found'));
        }

        // Parse the date or set it to now
        $date = $date ? Carbon::parse($date) : Carbon::now();
        if ($date->isPast()) {
            $date = Carbon::now();
        }

        $start_date = $date->copy();

        // Apply minimum booking lead time if set
        if ($provider->minimum_booking_lead_time_hours !== null) {
            $minimumStartDate = Carbon::now()->addHours($provider->minimum_booking_lead_time_hours)->startOfDay();
            if ($start_date->lessThan($minimumStartDate)) {
                $start_date = $minimumStartDate;
            }
        }

        // Apply maximum booking lead time if set, otherwise use default 60 days
        if ($provider->maximum_booking_lead_time_months !== null) {
            $end_of_two_month = Carbon::now()->addMonths($provider->maximum_booking_lead_time_months);
        } else {
            $end_of_two_month = $date->copy()->addDays(60);
        }

        $available_dates = [];

        // Loop through each day within the range
        for ($day = $start_date->copy(); $day->lte($end_of_two_month); $day->addDay()) {
            $day_of_week = $day->format('l');

            // Fetch operational hours
            $ops_hours = $provider->operationalHours()
                ->where('service_id', $service_id)
                ->where('day_of_week', $day_of_week)
                ->first();

            if (!$ops_hours) continue;  // No operational hours for this day

            // Calculate time slots and booked appointments
            $duration = $ops_hours->duration_in_minutes;
            $time_from = (int) Carbon::parse($ops_hours->start_time)->format('Hi'); // HHMM as integer
            $time_to = (int) Carbon::parse($ops_hours->end_time)->format('Hi');

            // Create an array for availability
            $slots = [];

            // Create slots and check for booked appointments
            while ($time_from < $time_to) {
                $is_in_off_hours = false;

                // Check for off hours
                foreach (
                    $provider->operationalOffHours()
                        ->where('service_id', $service_id)
                        ->where('day_of_week', $day_of_week)
                        ->get() as $off_hour
                ) {
                    $off_start = (int) Carbon::parse($off_hour->start_time)->format('Hi');
                    $off_end = (int) Carbon::parse($off_hour->end_time)->format('Hi');
                    if ($time_from >= $off_start && $time_from < $off_end) {
                        $is_in_off_hours = true;
                        break;
                    }
                }

                if (!$is_in_off_hours) {
                    $slots[] = $time_from;  // Store available slot
                }

                $time_from += $duration;  // Move to next slot
            }

            // Fetch booked appointments only if slots exist
            if (!empty($slots)) {
                $current_date = $day->format('Y-m-d');
                $booked_count = \App\Models\AppointmentService::where('date', $current_date)
                    ->whereHas('appointment', function ($query) use ($provider) {
                        $query->where('service_provider_id', $provider->id)
                            ->whereIn('status_id', [1, 2, 6]);
                    })
                    ->count();  // Get count of booked appointments

                // If booked count matches expected slots, skip this date
                if ($booked_count >= count($slots)) {
                    continue;
                }

                // Check held time slots for the day
                $heldTimeSlots = $service->heldTimeSlots()
                    ->where('service_provider_id', $provider_id)
                    ->whereDate('date', $current_date)
                    ->where('expires_at', '>', Carbon::now())
                    ->pluck('timeSlot')  // Get held time slots
                    ->map(fn($timeSlot) => (int) Carbon::parse($timeSlot)->format('Hi')) // Convert to integer format
                    ->toArray();

                // Remove held time slots from available slots
                $slots = array_diff($slots, $heldTimeSlots);

                // If there are available slots for the day, add the date to available_dates
                if (!empty($slots)) {
                    $available_dates[] = $current_date;
                }
            }
        }

        return [
            'available_dates' => $available_dates,
        ];
    }


    protected
    function validateAppointmentTime()
    {
        // $day_of_date = date('l', strtotime($date));
        // $ops_hours = $provider->operationalHours()->where('day_of_week', $day_of_date)->first();
        // $date = Carbon::parse($date)->format('Y-m-d');

        // if (! $ops_hours) {
        //     throw new \Exception('Provider is not available on this day');
        // }

        // $duration = $ops_hours->duration_in_minutes;

        // $time_from = \Carbon\Carbon::parse($time_from)->format('H:i:s');
        // $time_to = \Carbon\Carbon::parse($time_from)->addMinutes($duration)->format('H:i:s');

        // if ($ops_hours->start_time > $time_from || $ops_hours->end_time < $time_to) {
        //     throw new \Exception('Provider is not available on this time');
        // }

        // $booked_appointments = $provider->appointments()->where('date', $date)->get();

        // if ($booked_appointments->count() > 0) {
        //     foreach ($booked_appointments as $booked_appointment) {
        //         $booked_appointment_time_from = $booked_appointment->time_from->format('H:i:s');
        //         $booked_appointment_time_to = $booked_appointment->time_to->format('H:i:s');
        //         if ($booked_appointment_time_from <= $time_from && $booked_appointment_time_to > $time_from) {
        //             throw new \Exception('Provider is not available on this time');
        //         }
        //     }
        // }
    }


    /**
     * @throws Exception
     */
    public
    function markAsComplete($appointment): JsonResponse {
        $last_appointment_service = $appointment->appointmentServices
            ->sortByDesc(function ($service) {
                return Carbon::parse($service->date)->format('Y-m-d') . ' ' . $service->end_time;
            })
            ->first();

        $last_service_end_datetime = Carbon::parse($last_appointment_service->date)->setTimeFromTimeString($last_appointment_service->end_time);

        // if (Carbon::parse($last_appointment_service->date)->isAfter(today())) {
        //     throw new Exception(__('Appointment is not yet completed'));
        // }


        // if ($last_service_end_datetime->greaterThan(Carbon::now())) {
        //     throw new Exception(__('Appointment is not yet completed'));
        // }

        $appointment->state()->complete();
        // Check for the referral code if this is the customer's first appointment
        if ($appointment->customer && $appointment->customer->appointments()->where('status_id', AppointmentStatus::Completed->value)->count() === 1) {
            $referralId = $appointment->customer->referral_id; // Assuming 'referral_code' field in Customer

            if ($referralId) {
                $this->checkReferId($referralId, $appointment->customer);
            }
        }

        if ($appointment->customer) {
            //increment customer loyalty points
            $this->incrementCustomerPoints($appointment->customer, $appointment->total);
        }

        //requestForpayment to customer
        // notification (only for Cash payment)
        if ($appointment->payment_method_id) {
            try {

                $paymentMethod = PaymentMethod::find($appointment->payment_method_id);

                if ($paymentMethod && strtolower($paymentMethod->name) === 'cash') {
                    $appointment->customer->user
                        ->notify(new RequestPaymentNotification($appointment));
                }
            } catch (\Exception $e) {
                Log::error('Failed to send AppointmentCompleteNotification', [
                    'appointment_id'      => $appointment->id,
                    'provider_user_id'     => $appointment->serviceProvider->user_id ?? null,
                    'payment_method_name' => $paymentMethod->name ?? null,
                    'exception'            => $e->getMessage(),
                    'trace'                => $e->getTraceAsString(),
                ]);
            }
        }




        return response()->json([
            'message' => __('Appointment marked as complete'),
        ], 200);
    }

    public function checkReferId($referralId, $customer)
    {
        $referral = Customer::where('id', $referralId)->first();
        //not found code
        if ($referral) {
            //find setting
            $referral_bonus = app(RewardsSettings::class)->referral_bonus;
            if ($referral_bonus) {
                //charge referral wallet
                if ($referral_bonus > 0) {
                    $this->chargeReferralWallet($referral, $referral_bonus, $customer);
                }
            }
        }
    }

    public function chargeReferralWallet($referral, $amount, $customer)
    {
        $description = 'Congrats! Your referral was a success! 🎉 You\'ve earned [ ' . $amount . ' ] in your wallet. Check it out and keep sharing for more rewards!';
        $description_ar = 'تهانينا! لقد نجح الإحالة الخاصة بك! 🎉 لقد حصلت على [ ' . $amount . ' ] في محفظتك. تحقق منها واستمر في المشاركة للحصول على المزيد من المكافآت!';
        $wallet = $referral->user->wallet;
        (new CreateWalletTransactionMutation())->handle($wallet, $amount, TransactionType::IN, $description, true, $description_ar);
    }

    public function incrementCustomerPoints($customer, $total)
    {
        //find setting
        $riyals_per_point = app(RewardsSettings::class)->riyals_per_point;
        if ($riyals_per_point) {
            //charge referral wallet
            if ($riyals_per_point > 0) {
                $points = (int)$total / $riyals_per_point;
                $description = 'Congrats! Your appointment is complete! 🎉 You\'ve earned [ ' . $points . ' ] in your loyalty points!';
                $description_ar = 'تهانينا! تم اكتمال موعدك! 🎉 لقد حصلت على [ ' . $points . ' ] من نقاط الولاء!';
                (new CreatePointTransactionMutation())->handle($customer, $points, TransactionType::IN, $description, true, $description_ar);
            }
        }
    }

    /**
     * Change the payment method for remaining payment
     *
     * @param Appointment $appointment
     * @param int $payment_method_id
     * @return AppointmentResource
     */
    public function changeRemainingPaymentMethod(Appointment $appointment, int $payment_method_id): AppointmentResource
    {
        $appointment->remaining_payment_method_id = $payment_method_id;
        $appointment->save();

        return AppointmentResource::make($appointment->load('serviceProvider', 'services', 'appointmentServices', 'customer', 'promoCode', 'paymentMethod', 'depositPaymentMethod', 'remainingPaymentMethod', 'invoice'));
    }
}
