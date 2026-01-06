<?php

namespace App\StateMachines\Appointment;

use App\Actions\Wallet\Mutations\CreateWalletTransactionMutation;
use App\Enums\AppointmentStatus;
use App\Notifications\AppointmentNotification;
use App\Models\Enums\TransactionType;
use App\Notifications\ConfirmAppointmentNotification;
use App\Notifications\RejectAppointmentNotification;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PendingState extends BaseAppointmentState
{
    public function confirm(): void
    {
       $paymentMethod = $this->appointment->paymentMethod;

       if (
        $paymentMethod &&
        strtolower($paymentMethod->name) === 'cash'
    ) {
        if ($this->appointment->serviceProvider->user_id !== auth()->id()) {
            throw new \Exception('Only the appointment service provider can confirm the appointment');
        }
    }
        $this->appointment->update([
            'status_id' => AppointmentStatus::Confirmed->value,
            'changed_status_at' => now(),
        ]);

        if ($this->appointment->gift_card_id) {
            $this->appointment->giftCard->update([
                'status_id' => 2,
            ]);
        }
        //notification
        try {
            $this->appointment->customer->user->notify(new ConfirmAppointmentNotification($this->appointment));
        } catch (\Exception $e) {
            Log::info($e);
        }

    }

    /**
     * @throws Exception
     */
    public function reject(): void
    {
        $appointment = $this->appointment;

        if ($appointment->serviceProvider->user_id !== auth()->id()) {
            throw new \Exception('Only the appointment service provider can reject the appointment');
        }

        $appointment_date = Carbon::create($appointment->services->first()->pivot->date)->setTimeFromTimeString($appointment->services->first()->pivot->start_time);

        $is_appointment_past_limit = $appointment_date->diffInMinutes(Carbon::now()) < 360;

        if ($is_appointment_past_limit) {
            throw new Exception('Appointment cannot be rejected less than 6 hours before the appointment');
        }

        DB::beginTransaction();
        $appointment->update([
            'status_id' => AppointmentStatus::Rejected->value,
            'changed_status_at' => now(),
        ]);

        //return money to user wallet
          if ($appointment->payment_status == 'paid' || $appointment->payment_status == 'partially_paid') {
              $wallet = $appointment->customer->user->wallet;
              $total = $appointment->total_payed;
              if ($total > 0) {
                  (new CreateWalletTransactionMutation())->handle(
                      $wallet,
                      $total,
                      TransactionType::IN,
                      "Appointment #$appointment->id rejected",
                      false,
                      " رفض موعد رقم : $appointment->id"
                  );
              }

          }
          //return promo code
        if ($appointment->promo_code_id !== null) {
            if($appointment->promoCode){
                $appointment->promoCode->decrement('count_of_redeems');
            }
            $appointment->update([
                'promo_code_id' => null,
            ]);
        }
         if ($appointment->gift_card_id !== null) {

             $appointment->update([
                 'gift_card_id' => null,
             ]);

             $appointment->giftCard->update([
                 'is_used' => false,
                 'used_by' => null,
                 'appointment_id' => null,
             ]);
         }
         //return loyalty discount
        if ($appointment->loyalty_discount_customer_id !== null) {
            if($appointment->loyaltyDiscountCustomer){
                $appointment->loyaltyDiscountCustomer->update(['is_used' => false]);
            }
            $appointment->update([
                'loyalty_discount_customer_id' => null,
            ]);
        }
        DB::commit();
        //notification
           try {
               $appointment->customer->user->notify(new RejectAppointmentNotification($appointment));
           } catch (\Exception $e) {
               Log::info($e);
           }
    }

    public function cancel(): void
    {

        $appointment = $this->appointment;

        if ($appointment->customer->user_id !== auth()->id() && $appointment->serviceProvider->user_id !== auth()->id()) {
            throw new \Exception('Either the appointment customer or the appointment provider can cancel the appointment');
        }

        // Determine who is cancelling
        $isProviderCancelling = ($appointment->serviceProvider->user_id === auth()->id());

        // Calculate refund based on cancellation policy
        $cancellationPolicyService = new \App\Services\CancellationPolicyService();
        $refundInfo = $cancellationPolicyService->calculateRefund($appointment, $isProviderCancelling);

        DB::beginTransaction();
        $appointment->update([
            'status_id' => AppointmentStatus::Cancelled->value,
            'changed_status_at' => now(),
        ]);

        // Handle refund based on policy
        if ($appointment->payment_status == 'paid' || $appointment->payment_status == 'partially_paid') {
            $refundAmount = $refundInfo['refund_amount'];

            if ($refundInfo['refund_percentage'] == 100 && $refundAmount > 0) {
                // Refund to customer (deposit only for provider, full amount for customer)
                $customerWallet = $appointment->customer->user->wallet;
                $refundReason = $isProviderCancelling
                    ? "Appointment #$appointment->id canceled by provider - Deposit refund"
                    : "Appointment #$appointment->id canceled - Full refund";
                $refundReasonAr = $isProviderCancelling
                    ? "الغاء موعد رقم : $appointment->id من قبل مقدم الخدمة - استرجاع العربون"
                    : "الغاء موعد رقم : $appointment->id - استرجاع كامل";

                // Add refund to customer wallet (observer will update balance)
                (new CreateWalletTransactionMutation())->handle(
                    $customerWallet,
                    $refundAmount,
                    TransactionType::IN,
                    $refundReason,
                    false,
                    $refundReasonAr
                );

                // Manually deduct from provider's pending balance (observer doesn't handle this correctly)
                $providerWallet = $appointment->serviceProvider->user->wallet;
                $providerWallet->pending_balance = max(0, $providerWallet->pending_balance - $refundAmount);
                $providerWallet->save();
            } else {
                // No refund - provider keeps the money (only when customer cancels late)
                // Money already in provider's pending balance, no action needed
            }
        }

        //return promo code
        if ($appointment->promo_code_id !== null) {
            if($appointment->promoCode){
                $appointment->promoCode->decrement('count_of_redeems');
            }
            $appointment->update([
                'promo_code_id' => null,
            ]);
        }

        if ($appointment->gift_card_id !== null) {

            $appointment->update([
                'gift_card_id' => null,
            ]);

            $appointment->giftCard->update([
                'is_used' => false,
                'used_by' => null,
                'appointment_id' => null,
            ]);
        }

        //return loyalty discount
        if ($appointment->loyalty_discount_customer_id !== null) {
            if($appointment->loyaltyDiscountCustomer){
                $appointment->loyaltyDiscountCustomer->update(['is_used' => false]);
            }
            $appointment->update([
                'loyalty_discount_customer_id' => null,
            ]);
        }
         DB::commit();
        //notification
         try {
             $appointment->serviceProvider->user->notify(new RejectAppointmentNotification($appointment));
         } catch (\Exception $e) {
             Log::info($e);
         }
    }

    public function rescheduleRequest(): void
    {
        $appointment = $this->appointment;

        if ($appointment->serviceProvider->user_id !== auth()->id()) {
            throw new \Exception('Only the appointment provider can request reschedule the appointment');
        }

        $appointment->update([
            'status_id' => AppointmentStatus::RescheduleRequest->value,
            'previous_status_id' => $appointment->status_id,
            'changed_status_at' => now(),
        ]);

    }
}
