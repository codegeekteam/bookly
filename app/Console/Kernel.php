<?php

namespace App\Console;

use App\Jobs\DeleteExpiredCartItemsJob;
use App\Jobs\RejectExpiredPendingAppointmentsJob;
use App\Jobs\RejectExpiredPendingAppointmentsServicesJob;
use App\Jobs\RejectUnpaidAppointmentsJob;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();
        $schedule->job(new RejectExpiredPendingAppointmentsJob())->hourly();
      //  $schedule->job(new RejectUnpaidAppointmentsJob())->everyMinute();
        $schedule->job(new RejectExpiredPendingAppointmentsServicesJob())->everyFiveMinutes();
        $schedule->job(new DeleteExpiredCartItemsJob())->everyFiveMinutes();

        // Group eligible payouts on scheduled payout days (runs daily at 6 AM)
        $schedule->command('app:group-payouts')->hourly(); //->everyMinute(); //->dailyAt('06:00');      
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }



}
