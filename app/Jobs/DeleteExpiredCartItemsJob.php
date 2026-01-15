<?php

namespace App\Jobs;

use App\Actions\Wallet\Mutations\CreateWalletTransactionMutation;
use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\CartItem;
use App\Notifications\AppointmentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use App\Models\Enums\TransactionType;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

class DeleteExpiredCartItemsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct()
    {
    }

    public function handle(): void
    {
        $today = Carbon::today();
        $now = Carbon::now();  // Current date and time
        $tenMinutesFromNow = $now->copy()->addMinutes(10);
        try {
            CartItem::where('picked_date', '<', $today->toDateString())->delete();
            // Delete items where the picked_date is today, and the timeslot is earlier than now
            CartItem::where('picked_date', $today->toDateString())
                ->whereRaw("STR_TO_DATE(time_slot, '%h:%i %p') < ?", [$tenMinutesFromNow->format('H:i:s')])
                ->delete();
        } catch (\Exception $e) {
            \Log::error('Error while delete expired cart items ' . $e->getMessage());
            // Optionally rethrow the exception if you want to log it and fail the job
            throw $e;
        }
    }
}
