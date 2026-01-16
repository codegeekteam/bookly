<?php

namespace App\Jobs;

use App\Actions\Wallet\Mutations\CreateWalletTransactionMutation;
use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Notifications\RejectAppointmentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use App\Models\Enums\TransactionType;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

class RejectUnpaidAppointmentsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct()
    {
    }

    public function handle(): void
    {
        $now = Carbon::now();
        $fiveMinutesFromNow = $now->subMinutes(10);
        try {
            $expired_appointments = Appointment::where('status_id', AppointmentStatus::Pending->value)
                ->where('created_at', '<', $fiveMinutesFromNow)->whereIn('payment_status', ['partially_paid', 'unpaid'])
                ->get();
            foreach ($expired_appointments as $appointment) {
                $appointment->update(['status_id' => AppointmentStatus::Rejected->value]);
                //check total payed and return the amount to user wallet
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
                //return promo code
                if ($appointment->promo_code_id !== null) {
                    if ($appointment->promoCode) {
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
                //notification
                try {
                    $appointment->customer->user->notify(new RejectAppointmentNotification($appointment));
                } catch (\Exception $e) {
                    Log::info($e);
                }
            }
        } catch (\Exception $e) {
            \Log::error('Error while rejecting unpaid appointments: ' . $e->getMessage());
            // Optionally rethrow the exception if you want to log it and fail the job
            throw $e;
        }
    }
}
