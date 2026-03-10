<?php

namespace App\Jobs;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Notifications\ReminderAppointmentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RemiderAppointmentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $appointment;

    /**
     * Create a new job instance.
     */
    public function __construct(Appointment $appointment)
    {
         $this->appointment = $appointment;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
         $appointment = $this->appointment->fresh();

        if (!$appointment) {
            return;
        }

        $user = $appointment->serviceProvider?->user;

        if (!$user) {
            return;
        }

        $user->notify(
            new ReminderAppointmentNotification($appointment, 'provider')
        );
    }
}
