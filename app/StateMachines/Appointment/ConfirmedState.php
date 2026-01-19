<?php

namespace App\StateMachines\Appointment;

use Exception;
use Carbon\Carbon;
use App\Models\RefundLog;
use App\Models\PaymentLog;
use App\Models\Appointment;
use App\Helpers\RefundHelper;
use App\Helpers\PayfortHelper;
use App\Models\AttachedService;
use App\Enums\AppointmentStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Models\Enums\TransactionType;
use App\Notifications\AppointmentNotification;
use App\Notifications\RequestPaymentNotification;
use App\Notifications\RejectAppointmentNotification;
use App\Notifications\CompletedAppoitmentNotification;
use App\Actions\Wallet\Mutations\CreateWalletTransactionMutation;

class ConfirmedState extends BaseAppointmentState
{
    public function complete(): void
    {
        $paymentMethod = $this->appointment->paymentMethod;

       if ($paymentMethod && strtolower($paymentMethod->name) === 'cash') {
        if ($this->appointment->serviceProvider->user_id !== auth()->id()) {
            throw new Exception('Only the appointment service provider can complete the appointment');
        }

    }

        $this->appointment->update([
            'status_id' => AppointmentStatus::Completed->value,
            'changed_status_at' => now(),
            'remaining_amount' => 0,
            'payment_status' => 'paid',
            'total_payed' => $this->appointment->total,
        ]);
        $wallet = $this->appointment->serviceProvider->user->wallet;
        $amount=$this->appointment->amount_due;
        $wallet->update([
            'balance' => $wallet->balance + $amount,
            'pending_balance' => $wallet->pending_balance - $amount,
        ]);

            \Log::info('CompletedAppoitmentNotification reached in confirm state complete method');  
        //notification
           try {
               $this->appointment->customer->user->notify(new CompletedAppoitmentNotification($this->appointment));
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

        if($is_appointment_past_limit){
            throw new Exception('Appointment cannot be rejected less than 6 hours before the appointment');
        }
        DB::beginTransaction();

        $appointment->update([
            'status_id' => AppointmentStatus::Rejected->value,
            'changed_status_at' => now(),
        ]);
        DB::commit(); 
        $paymentMethod = $appointment->paymentMethod;
        if ($paymentMethod && strtolower($paymentMethod->name) === 'card') { 
            $response = $this->initiateRefund($appointment, 'reject');
        }
 
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
        //return gift
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
  \Log::info('RejectAppointmentNotification reached in confirm state reject method');  
        //notification
           try {
               $appointment->customer->user->notify(new RejectAppointmentNotification($appointment, 'customer'));
           } catch (\Exception $e) {
               Log::info($e);
           }

    }


    /**
     * @throws Exception
     */
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

         DB::commit();
        $paymentMethod = $appointment->paymentMethod;
        if ($paymentMethod && strtolower($paymentMethod->name) === 'card') {      
            $response = $this->initiateRefund($appointment, 'cancel');
        }
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

        //return promo code
        if ($appointment->promo_code_id !== null) {
            if($appointment->promoCode){
                $appointment->promoCode->decrement('count_of_redeems');
            }
            $appointment->update([
                'promo_code_id' => null,
            ]);
        }

        //return gift
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
        \Log::info('RejectAppointmentNotification reached in confirm state -cancel method');  
        try {
            $appointment->serviceProvider->user->notify(new RejectAppointmentNotification($appointment, 'provider'));
        } catch (\Exception $e) {
            Log::info($e);
        }
    }

    /**
     * @throws Exception
     */
    public function RescheduleRequest(): void
    {
        $appointment = $this->appointment;
        $logged_user = auth()->user();



        if($logged_user->id != $appointment->serviceProvider->user_id && $logged_user->id != $appointment->customer->user_id){
            throw new Exception('Only the appointment customer or provider can request reschedule the appointment');
        }

            $appointment->update([
                'status_id' => AppointmentStatus::RescheduleRequest->value,
                'previous_status_id' => $appointment->status_id,
                'changed_status_at' => now(),
            ]);

    }

    public function PaymentRequest(): void
    {
        $paymentMethod = $this->appointment->paymentMethod; 

        $this->appointment->update([
            'status_id' => AppointmentStatus::PaymentRequest->value,
            'changed_status_at' => now(),
        ]);
          \Log::info('RequestPaymentNotification reached in confirm state request payment method');  
        //notification
           try {
               $this->appointment->customer->user->notify(new RequestPaymentNotification($this->appointment));
           } catch (\Exception $e) {
               Log::info($e);
           }
    
    }

    public function initiateRefund(Appointment $appointment, $type)
    {
        $paymentLog = PaymentLog::where('appointment_id', $appointment->id)->first();
        if(!$paymentLog || $paymentLog->mechant_reference == null) {
              \Log::info('paymentLog data insufficient');
        }
        //  $description = json_decode($appointment->service?->title, true);

         if($type == 'reject') {
         $total = $appointment->total_payed;
         }
         if($type == 'cancel') {
             // Determine who is cancelling
        $isProviderCancelling = ($appointment->serviceProvider->user_id === auth()->id());

        // Calculate refund based on cancellation policy
        $cancellationPolicyService = new \App\Services\CancellationPolicyService();
        $refundInfo = $cancellationPolicyService->calculateRefund($appointment, $isProviderCancelling);
            if ($appointment->payment_status == 'paid' || $appointment->payment_status == 'partially_paid') {
            $refundAmount = $refundInfo['refund_amount'];
            if ($refundInfo['refund_percentage'] == 100 && $refundAmount > 0) {        
                $total = $refundAmount;
            }
        }
         }

         $amount = round($total) * 100; //converted to sub unit

        $base_url = config('services.payfort.refund_url').'/FortAPI/paymentApi';
        $refund_data = [            
                        'command' => 'REFUND',
                        'access_code' =>  config('services.payfort.access_code'),
                        'merchant_identifier' =>  config('services.payfort.merchant_identifier'),
                        'merchant_reference' => $paymentLog->merchant_reference,
                        'amount' =>  $amount,
                        'currency' =>  'SAR',
                        'language' => 'en',
                        'fort_id' =>  $paymentLog->fort_id,            
                    ];
         $refund_data['signature'] = PayfortHelper::generateSignature($refund_data);
         $refund_data['order_description'] =  $paymentLog->appointment_id . '- Refund Request Processed'; 
         $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post($base_url, $refund_data); 

        //    $response = Http::asForm()->post(config('payfort.endpoint'), $params);

        \Log::info('REFUND PROCESSED RESPONSE STATUS', [
            'status' => $response->status(),
           // 'body'   => $response->body(),
        ]);
       
    // Split the merchant_reference into type and identifier
        $parts = explode('_', $response['merchant_reference']);

        if (count($parts) < 2) {
            return response()->json(['message' => 'Invalid ID format'], 200);
            // return response()->json(['message' => 'success'], 200);
        }
            if(count($parts) == 3) {
            $type = 'appointment'; 
            $identifier = $parts[1];
            $paymentType = 'remaining';
        }elseif(count($parts) == 2) {
             $type = $parts[0];
            $identifier = $parts[1];
        }      
if($response['response_code'] == '06000') {
        $refundHelper = new RefundHelper;
        RefundLog::create([
           'response_code' => $response['response_code'],
           'response_message' => $response['response_message'],
           'amount' => $response['amount'],
           'status' => $response['status'],
           'merchant_reference' => $response['merchant_reference'],          
           'response' => json_encode($response),
           'model_type' => $refundHelper->getMorphClassFromType($type),
           'model_id' => $identifier,
        ]);        
}else {
    \Log::info('Refund Failed');
}
       //  return $response->json();

}

}