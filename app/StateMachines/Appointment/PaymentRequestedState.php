<?php

namespace App\StateMachines\Appointment;

use Exception;
use App\Enums\AppointmentStatus;

class PaymentRequestedState extends BaseAppointmentState
{
    public function complete(): void
    {
        $paymentMethod = $this->appointment->paymentMethod;

        $this->appointment->update([
            'status_id' => AppointmentStatus::Completed->value,
            'changed_status_at' => now(),
        ]);
        $wallet = $this->appointment->serviceProvider->user->wallet;
        $amount=$this->appointment->amount_due;
        $wallet->update([
            'balance' => $wallet->balance + $amount,
            'pending_balance' => $wallet->pending_balance - $amount,
        ]);
    }
  
}
