<?php

namespace App\StateMachines\Appointment;

use App\Actions\Wallet\Mutations\CreateWalletTransactionMutation;
use App\Enums\AppointmentStatus;
use App\Helpers\RefundHelper;
use App\Mail\AppointmentRejectMail;
use App\Models\Appointment;
use App\Models\Enums\TransactionType;
use App\Models\PaymentLog;
use App\Models\RefundLog;
use App\Models\RefundSetting;
use App\Models\User;
use App\Notifications\AppointmentNotification;
use App\Notifications\ConfirmAppointmentNotification;
use App\Notifications\RejectAppointmentNotification;
use App\Notifications\RequestCancellationAdminNotification;
use App\Notifications\RequestCancellationCustomerNotification;
use App\Traits\RefundTrait;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PendingState extends BaseAppointmentState
{
    use RefundTrait;
    
    public function confirm(): void
    {
        $timeLimitHours = config('app.limit_hours');
        if(auth()->id() === $this->appointment->serviceProvider->user_id) {
            // if ($this->appointment->created_at->lt(now()->subHours($timeLimitHours))) {
            if ($this->appointment->created_at->lt(now()->subMinutes($timeLimitHours))) { //remve after testing
                throw new Exception('The time limit of {$timeLimitHours} hours exceeded. Cannot confirm this appointment');
            }
        }
       $paymentMethod = $this->appointment->paymentMethod;

       if ($paymentMethod && strtolower($paymentMethod->name) === 'cash') {
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
        $timeLimitHours = config('app.limit_hours');
        if(auth()->id() === $this->appointment->serviceProvider->user_id) {
           // if ($this->appointment->created_at->lt(now()->subHours($timeLimitHours))) {
           if ($this->appointment->created_at->lt(now()->subMinutes($timeLimitHours))) { //remve after testing
                throw new Exception('The time limit of {$timeLimitHours} hours exceeded. Cannot reject this appointment');
            }
        }
        
        $appointment = $this->appointment;

        if ($appointment->serviceProvider->user_id !== auth()->id()) {
            throw new \Exception('Only the appointment service provider can reject the appointment');
        }

        $appointment_date = Carbon::create($appointment->services->first()->pivot->date)->setTimeFromTimeString($appointment->services->first()->pivot->start_time);

        // $is_appointment_past_limit = $appointment_date->diffInMinutes(Carbon::now()) < 360;

        // if ($is_appointment_past_limit) {
        //     throw new Exception('Appointment cannot be rejected less than 6 hours before the appointment');
        // }

        DB::beginTransaction();
        $appointment->update([
            'status_id' => AppointmentStatus::Rejected->value,
            'changed_status_at' => now(),
        ]);
        DB::commit();   

      //  $refund_type = RefundSetting::find(1); 
       // if($refund_type->bank_account_refund == 1) { 
       $paymentMethod = $appointment->paymentMethod;
       if(Str::lower($paymentMethod) === 'card') {             
            $paymentLog = PaymentLog::where('appointment_id',$appointment->id)->first();
            if($paymentLog && $paymentMethod && strtolower($paymentMethod->name) === 'card') {  
                 \Log::info('Calling initiate Refund in  pending state reject method');    
                $response = $this->initiateRefund($appointment, 'reject');
                 \Log::info('Refund Initiate : '. $response);
            }
          \Log::info('Refund  skipped — no valid payment method');
        // }elseif($refund_type->wallet_refund == 1){
        }elseif(Str::lower($paymentMethod->name) === 'wallet' || Str::lower($paymentMethod->name) === 'card and wallet'){
            DB::beginTransaction();
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
        }
        //notification
           try {
               $appointment->customer->user->notify(new RejectAppointmentNotification($appointment, 'customer'));
           } catch (\Exception $e) {
               Log::info($e);
           }
    }

    public function cancel(): void
    {

        $appointment = $this->appointment;

        // if ($appointment->customer->user_id !== auth()->id() && $appointment->serviceProvider->user_id !== auth()->id()) {
        //     throw new \Exception('Either the appointment customer or the appointment provider can cancel the appointment');
        // }
        if ($appointment->customer->user_id !== auth()->id()) {
            throw new \Exception('Only the appointment customer can cancel the appointment');
        }
        if ($appointment->serviceProvider->user_id === auth()->id()) {
            throw new \Exception('The appointment provider cannot cancel the appointment');
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
        DB::commit();     

        $bankRefund = false;
        $walletRefund = false;
        $method = $appointment->refund_method;
        if (!$method) {
           // $refund_type = RefundSetting::find(1);
           // $bankRefund   = $refund_type->bank_account_refund == 1;
           // $walletRefund = $refund_type->wallet_refund == 1;
            $paymentMethod = $appointment->paymentMethod;         
            $bankRefund   = Str::lower($paymentMethod) === 'card' ;
            $walletRefund = Str::lower($paymentMethod) === 'wallet' || Str::lower($paymentMethod) === 'card and wallet' ;            
        } else {
            $bankRefund   = $method === 'bank';
            $walletRefund = $method === 'wallet';
        }
        if($bankRefund) {
            $paymentMethod = $appointment->paymentMethod;
            $paymentLog = PaymentLog::where('appointment_id',$appointment->id)->first();
            if($paymentLog && $paymentMethod && strtolower($paymentMethod->name) === 'card') {  
                 \Log::info('Calling initiate Refund in  pending state cancel method');    
                $response = $this->initiateRefund($appointment, 'cancel');
                \Log::info('Refund Initiate : '. $response);
            }
             \Log::info('Refund  skipped — no valid payment method');
        }
        elseif($walletRefund) {
             DB::beginTransaction();
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
      //  }

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
        }
        
        //notification
         try {
            $appointment->serviceProvider->user->notify(new RejectAppointmentNotification($appointment, 'provider'));
            Mail::to($appointment->serviceProvider->email)->send(new AppointmentRejectMail($appointment));
         } catch (\Exception $e) {
             Log::info($e);
         }
    }

    public function rescheduleRequest(): void
    {
        $appointment = $this->appointment;
        $userId = auth()->id();

        // if ($appointment->serviceProvider->user_id !== auth()->id()) {
        //     throw new \Exception('Only the appointment provider can request reschedule the appointment');
        // }

        // if(($appointment->serviceProvider->user_id !== $userId) && ($appointment->customer->user_id !== $userId)){
        //     throw new Exception('Only the appointment customer or provider can reschedule the appointment');
        // }

        if ($appointment->customer->user_id !== auth()->id()) {
            throw new \Exception('Only the appointment customer can reschedule the appointment');
        }

        $timeLimitHours = config('app.limit_hours');   
       // if ($appointment->created_at->lt(now()->subHours($timeLimitHours))) {
        if ($appointment->created_at->lt(now()->subMinutes($timeLimitHours))) { //remve after testing
            throw new Exception('The time limit of {$timeLimitHours} hours exceeded. Cannot reschedule this appointment');
        }
        

        $appointment->update([
            'status_id' => AppointmentStatus::RescheduleRequest->value,
            'previous_status_id' => $appointment->status_id,
            'changed_status_at' => now(),
        ]);

    }

    public function cancellationRequest() : void
    {
        $appointment = $this->appointment;              
        if ($appointment->serviceProvider->user_id !== auth()->id()) {
            throw new \Exception('Only the appointment service provider can request cancellation of the appointment');
        }
        $this->appointment->update([
            'status_id' => AppointmentStatus::CancellationRequest->value,
            'changed_status_at' => now(),
        ]);
          \Log::info('RequestCancellationNotification reached in confirm state cancellation request method');  
        //notification
            $admin = User::find(1);
           try {
               $this->appointment->customer->user->notify(new RequestCancellationCustomerNotification($this->appointment));
                $admin->notify(new RequestCancellationAdminNotification($this->appointment));
           } catch (\Exception $e) {
               Log::info($e);
           }

    }
    
}
