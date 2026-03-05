<?php

namespace App\Console\Commands;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Notifications\ReminderAppointmentNotification;
use Illuminate\Console\Command;

class RemiderAppointment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:remider-appointment';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send reminders to confirm or reject an appointment within allowed time limit';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $timeLimitHours =  (config('app.limit_hours') ?? 24) - 1;
        $appointments = Appointment::where('status_id', AppointmentStatus::Pending->value)->where('created_at', '<',now()->subHours($timeLimitHours))->get();
        foreach($appointments as $appointment) {  
           $appointment->serviceProvider->user->notify(new ReminderAppointmentNotification($appointment, 'provider'));
        }
    }
}
