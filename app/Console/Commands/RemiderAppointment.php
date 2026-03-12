<?php

namespace App\Console\Commands;

use App\Enums\AppointmentStatus;
use App\Jobs\RemiderAppointmentJob;
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
         $timeLimitHours = (config('app.limit_hours') ?? 24) - config('app.sub_hours');

        Appointment::where('status_id', AppointmentStatus::Pending->value)
            ->where('reminder_sent', false)
            ->where('created_at', '<', now()->subHours($timeLimitHours))
            ->chunkById(100, function ($appointments) {

                foreach ($appointments as $appointment) {

                    $appointment->update(['reminder_sent' => true]);
                    RemiderAppointmentJob::dispatch($appointment);                    

                }

            });
    }
}
