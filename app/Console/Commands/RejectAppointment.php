<?php

namespace App\Console\Commands;

use App\Actions\Wallet\Mutations\CreateWalletTransactionMutation;
use App\Enums\AppointmentStatus;
use App\Mail\AppointmentAutoRejectCustomerMail;
use App\Mail\AppointmentAutoRejectProviderMail;
use App\Models\Appointment;
use App\Models\Enums\TransactionType;
use App\Models\PaymentLog;
use App\Notifications\RejectAppointmentCustomerNotification;
use App\Notifications\RejectAppointmentProviderNotification;
use App\Traits\RefundTrait;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class RejectAppointment extends Command
{
    use RefundTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:reject-appointment';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically reject appointments which are not considered for confirm or reject by Service Provider within allowed time limit';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $appointments = Appointment::where('status_id', AppointmentStatus::Pending->value)->where('created_at', '<',now()->subHours(24))->get();
        foreach($appointments as $appointment) {           

        $appointment_date = Carbon::create($appointment->services->first()->pivot->date)->setTimeFromTimeString($appointment->services->first()->pivot->start_time);

        DB::beginTransaction();
        $appointment->update([
            'status_id' => AppointmentStatus::Rejected->value,
            'changed_status_at' => now(),
        ]);

        $paymentMethod = $appointment->paymentMethod;
        $paymentLog = PaymentLog::where('appointment_id', $appointment->id)->first();
        if($paymentLog && $paymentMethod && strtolower($paymentMethod->name) === 'card') {      
            $response = $this->initiateRefund($appointment, 'reject');
            \Log::info('Refund Initiate in auto reject after 24 hrs : '. $response);
        }
          \Log::info('Refund  skipped — no valid payment method');

        //return money to user wallet
        /*  if ($appointment->payment_status == 'paid' || $appointment->payment_status == 'partially_paid' && strtolower($paymentMethod->name) === 'wallet') {
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
        DB::commit();
        //notification and mail
           try {
                $appointment->customer->user->notify(new RejectAppointmentCustomerNotification($appointment));
                $appointment->customer->user->notify(new RejectAppointmentProviderNotification($appointment));
                Mail::to($appointment->customer->email)->send(new AppointmentAutoRejectCustomerMail($appointment));
                Mail::to($appointment->serviceProvider->email)->send(new AppointmentAutoRejectProviderMail($appointment));
           } catch (\Exception $e) {
               \Log::info($e);
           }
        }
    }
}
