<?php

use App\Enums\AppointmentStatus;

use App\Models\Appointment;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
Route::get('privacy-policy', [\App\Http\Controllers\SettingController::class, 'privacyPolicy'])->name('privacyPolicy');
Route::get('terms-conditions', [\App\Http\Controllers\SettingController::class, 'termsConditions'])->name('termsConditions');
Route::get('/contact', [\App\Http\Controllers\SettingController::class, 'contact'])->name('contactus');
Route::get('/share/provider/{id}', [\App\Http\Controllers\AppDownloadController::class, 'showProvider'])->name('app.download.provider');
 /*Route::get('/test', function () {
   $appointment = Appointment::find(460);
      $normalizedAmount = $appointment->remaining_amount;
         $type = 'appointment'; 
         $identifier = 460;
        $paymentType = 'remaining';
        $isRemainingPayment = true;
        $newTotal = $normalizedAmount + ($appointment->total_payed ?? 0);
        $isPaid = $newTotal >= $appointment->amount_due;

        if ($appointment->remaining_amount) {
            $appointment->remaining_payment_status = $isPaid ? 'paid' : 'pending';
        }

        $appointment->payment_status = $isPaid ? 'paid' : 'partially_paid';
        $appointment->card_amount = ($appointment->card_amount ?? 0) + $normalizedAmount;
        $appointment->total_payed = $newTotal;
        \Log::info('Process remaining Payment', ['appintment_payment_remaining_status' => $appointment->remaining_payment_status]);
        $appointment->save();
         \Log::info('Status id Value: ' . $appointment->status_id);
        \Log::info('Status payment status Value: ' . $appointment->payment_status);

          //  $appointment->refresh();
                    //  \Log::info('Status id Value: ' . $appointment->status_id);
                    //            \Log::info('Status payment status Value: ' . $appointment->payment_status);

        \Log::info('Completed Enum Value: ' . AppointmentStatus::Completed->value);
      

        if ($appointment->payment_status === 'paid' && $appointment->status_id !== AppointmentStatus::Completed->value) {  //By Sreeja             
                 \Log::info('Status id Value inside if: ' . $appointment->status_id);
                               \Log::info('Status payment status Value inside if: ' . $appointment->payment_status);   
          \Log::info('  $this->markAsComplete($appointment);');
           $appointment->state()->complete();
                \Log::info('executed in feedback api');
        }
         \Log::info('success');
         dd('success');
         $arr = [
        "appointment_id" => "661",
        "date" => "2026-03-04",
        "slots" => [
          '{
            "service_id": "2",
            "timeslot": "06:00"
          }',
           '{
            "service_id": "3",
            "timeslot": "07:00"
          }'
        ]
         ];
$slot = $arr['slots'];
           foreach($slot as $slt) {
            $slot_arr = json_decode($slt,true);
            $time_slots[] = $slot_arr['timeslot'];
            $service_ids[] = $slot_arr['service_id'];
           // $rescheduleTime[] = Carbon::parse($slot_arr['timeslot']);
        }
        dd($service_ids);
});*/