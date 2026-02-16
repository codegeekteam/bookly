<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\PaymentLog;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BatAppointmentsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $appointmentIds = [617,618,619,620];       
        $appointments = Appointment::whereIn('id', $appointmentIds)->get();
        foreach($appointments as $appointment)
        {
            $appointment->update([
                'payment_status' => 'partially_paid',
                'deposit_payment_status' => 'paid',
                'card_amount' => $appointment->deposit_amount,
                'total_payed' => $appointment->deposit_amount,
            ]);            
           // $appointment->delete();
        }
        // $payment_logs = PaymentLog::whereIn('appointment_id',$appointmentIds)->get();
        // foreach($payment_logs as $payment_log)
        // {                  
        //     $payment_log->delete();
        // }
    }
}
