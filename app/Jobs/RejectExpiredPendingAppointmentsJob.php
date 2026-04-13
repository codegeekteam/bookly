<?php

namespace App\Jobs;

use App\Actions\Wallet\Mutations\CreateWalletTransactionMutation;
use App\Enums\AppointmentStatus;
use App\Mail\AppointmentAutoRejectCustomerMail;
use App\Mail\AppointmentAutoRejectProviderMail;
use App\Models\Appointment;
use App\Models\PaymentLog;
use App\Notifications\RejectAppointmentCustomerNotification;
use App\Notifications\RejectAppointmentProviderNotification;
use App\Traits\RefundTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class RejectExpiredPendingAppointmentsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, RefundTrait;

    public function __construct()
    {
    }

    public function handle(): void
    {
        $twentyFourHoursAgo = Carbon::now()->subHours(24);//to do 24 after testing

        try {
            $expired_appointments = Appointment::where('status_id', AppointmentStatus::Pending->value)
                ->where('created_at', '<', $twentyFourHoursAgo)
                ->get();

            foreach ($expired_appointments as $appointment) {
                $appointment->update([
                                        'status_id' => AppointmentStatus::Rejected->value,
                                        'changed_status_at' => now()
                                    ]);                          
                     
                //check total payed and return the amount to user wallet
                if ($appointment->payment_status == 'paid' || $appointment->payment_status == 'partially_paid') { 
                    $paymentMethod = $appointment->paymentMethod;
                    $paymentLog = PaymentLog::where('appointment_id', $appointment->id)->first();
                    if($paymentLog && $paymentMethod && strtolower($paymentMethod->name) === 'card') {      
                        $response = $this->initiateRefund($appointment, 'reject');
                        \Log::info('Refund Initiate in auto reject after 24 hrs : '. $response);
                    }
                    \Log::info('Refund  skipped — no valid payment method'); 
                      //return money to user wallet
                        /*  if (strtolower($paymentMethod->name) === 'wallet') {
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
                        } */                   
                }
               
             
             
                //notification
                try {
                    $appointment->customer->user->notify(new RejectAppointmentCustomerNotification($appointment));
                    $appointment->serviceProvider->user->notify(new RejectAppointmentProviderNotification($appointment));
                    Mail::to($appointment->customer->email)->send(new AppointmentAutoRejectCustomerMail($appointment));
                    Mail::to($appointment->serviceProvider->email)->send(new AppointmentAutoRejectProviderMail($appointment));
                } catch (\Exception $e) {
                    Log::info($e);
                }
            }
        } catch (\Exception $e) {
            \Log::error('Error while rejecting expired pending appointments: ' . $e->getMessage());
            // Optionally rethrow the exception if you want to log it and fail the job
            throw $e;
        }
    }
}
