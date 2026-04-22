<?php

namespace App\Jobs;

use App\Actions\Wallet\Mutations\CreateWalletTransactionMutation;
use App\Enums\AppointmentStatus;
use App\Mail\AppointmentAutoRejectAfterOneHOurCustomerMail;
use App\Mail\AppointmentAutoRejectAfterOneHOurProviderMail;
use App\Mail\AppointmentRejectMail;
use App\Models\Appointment;
use App\Models\Enums\TransactionType;
use App\Models\PaymentLog;
use App\Notifications\AppointmentNotification;
use App\Notifications\RejectAppointmentAfterOneHourCustomerNotification;
use App\Notifications\RejectAppointmentAfterOneHourProviderNotification;
use App\Notifications\RejectAppointmentNotification;
use App\Traits\RefundTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class RejectExpiredPendingAppointmentsServicesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, RefundTrait;



    public function __construct()
    {
    }

    public function handle(): void
    {
        $now = Carbon::now();
         $tenMinutesFromNow= $now->addMinutes(10)->format('H:i:s');
      //  $tenMinutesFromNow = $now->copy()->addHour()->format('H:i:s'); //oneHourFromNow
        try {
            $expired_appointments = Appointment::where('status_id', AppointmentStatus::Pending->value)
                ->whereHas('services', function ($query) use ($now,$tenMinutesFromNow) {
                $query->whereDate('date', $now->toDateString()) // Match the service date
                ->where('start_time', '<=',$tenMinutesFromNow); // Check if within 10 minutes
            })->get();
            foreach ($expired_appointments as $appointment) {
                $appointment->update([
                    'status_id' => AppointmentStatus::Rejected->value
                ]);
                //check total payed and return the amount to card
                if ($appointment->payment_status == 'paid' || $appointment->payment_status == 'partially_paid') {
                      $paymentMethod = $appointment->paymentMethod;
                    $paymentLog = PaymentLog::where('appointment_id', $appointment->id)->first();
                    if($paymentLog && $paymentMethod && strtolower($paymentMethod->name) === 'card') {      
                        $response = $this->initiateRefund($appointment, 'reject');
                        \Log::info('Refund Initiate in auto reject after appointment start time : '. $response);
                    }
                    \Log::info('Refund  skipped — no valid payment method'); 
                        //return money to user wallet
                if (strtolower($paymentMethod->name) === 'wallet' || strtolower($paymentMethod) === 'card and wallet') {
                    $wallet = $appointment->customer->user->wallet;
                    $total=$appointment->total_payed;
                    if($total>0){
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
                    $appointment->update(['promo_code_id' => null,]);
                }
                //return loyalty discount
                if ($appointment->loyalty_discount_customer_id !== null) {
                    if($appointment->loyaltyDiscountCustomer){
                        $appointment->loyaltyDiscountCustomer->update(['is_used' => false]);
                    }
                    $appointment->update(['loyalty_discount_customer_id' => null]);
                }
                    
                }
                //notification
                \Log::info('reached notification in after start reject');
                try {           
                        $appointment->customer->user->notify(new RejectAppointmentAfterOneHourCustomerNotification($appointment));
                        $appointment->serviceProvider->user->notify(new RejectAppointmentAfterOneHourProviderNotification($appointment));
                        Mail::to($appointment->customer->email)->send(new AppointmentAutoRejectAfterOneHOurCustomerMail($appointment));
                        Mail::to($appointment->serviceProvider->email)->send(new AppointmentAutoRejectAfterOneHOurProviderMail($appointment));
                } catch (\Exception $e) {
                    Log::info($e);
                }
            }
        }catch (\Exception $e) {
            \Log::error('Error while rejecting expired pending appointments: ' . $e->getMessage());
            // Optionally rethrow the exception if you want to log it and fail the job
            throw $e;
        }
    }
}
