<?php

namespace App\StateMachines\Appointment;

use App\Enums\AppointmentStatus;
use App\Notifications\ConfirmAppointmentNotification;
use Exception;
use Illuminate\Support\Facades\Log;

class RescheduleRequestState extends BaseAppointmentState
{
    public function pending(): void
    {
        if (auth()->id() !== $this->appointment->serviceProvider->user_id) {
            throw new Exception('Only the appointment service provider can change the appointment');
        }

        $this->appointment->update([
            'status_id' => AppointmentStatus::Pending->value,
        ]);

    }

    /**
     * @throws Exception
     */
    public function confirm(): void
    {
        if (auth()->id() !== $this->appointment->customer_id) {
            throw new Exception('Only the appointment customer can confirm the appointment');
        }

        $this->appointment->update([
            'status_id' => AppointmentStatus::Confirmed->value,
            'changed_status_at' => now(),
        ]);

        // Send confirmation notification
        try {
            $this->appointment->customer->user->notify(new ConfirmAppointmentNotification($this->appointment));
        } catch (\Exception $e) {
            Log::info($e);
        }
    }
}
