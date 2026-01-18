<?php

namespace App\Observers;

use App\Enums\AppointmentStatus;
use App\Mail\AppointmentConfirmedMail;
use App\Mail\AppointmentRejectedMail;
use App\Models\Appointment;
use App\Services\ChatService;
use App\Services\PayoutService;
use Illuminate\Support\Facades\Mail;

class AppointmentObserver
{
    protected ChatService $chatService;
    protected PayoutService $payoutService;

    public function __construct(ChatService $chatService, PayoutService $payoutService)
    {
        $this->chatService = $chatService;
        $this->payoutService = $payoutService;
    }

    /**
     * Handle the Appointment "updated" event.
     */
    public function updated(Appointment $appointment): void
    {
          \Log::info('Appointment observer reached', [
                'id' => $appointment->id,
                'old' => $appointment->getOriginal('status_id'),
                'new' => $appointment->status_id,
            ]);
        // Check if appointment status changed
        if ($appointment->isDirty('status_id')) {
            \Log::info('Appointment updated fired', [
                'id' => $appointment->id,
                'old' => $appointment->getOriginal('status_id'),
                'new' => $appointment->status_id,
            ]);

            // Send email when status changes to confirmed
            if ($appointment->status_id === AppointmentStatus::Confirmed->value) {
                $this->sendConfirmationEmail($appointment);

                // Create or reactivate conversation when appointment is confirmed
               /* if (!$appointment->conversation) {
                    $this->chatService->createConversation($appointment);
                } else {
                    $appointment->conversation->update(['is_active' => true]);
                }*/
            } /* else {
                // Deactivate conversation when status changes away from Confirmed
                if ($appointment->conversation) {
                    $this->chatService->deactivateConversation($appointment->id);
                }
            }*/

            // Send email when status changes to rejected
            if ($appointment->status_id === AppointmentStatus::Rejected->value) {
                $this->sendRejectionEmail($appointment);
            }

            // Create deferred payout records when appointment is completed
            if ($appointment->status_id === AppointmentStatus::Completed->value) {
                   \Log::info('create DeferredPayouts For Appointment reached');
                $this->payoutService->createDeferredPayoutsForAppointment($appointment);
            }

            if ($appointment->status_id !== AppointmentStatus::Pending->value) {
                // Create or reactivate conversation when appointment is not pending
                if (!$appointment->conversation) {
                    $this->chatService->createConversation($appointment);
                } else {
                    $appointment->conversation->update(['is_active' => true]);
                } 
            }else {
                // Deactivate conversation when status changes to  Pending
                if ($appointment->conversation) {
                    $this->chatService->deactivateConversation($appointment->id);
                }
            }
        }
    }

    /**
     * Handle the Appointment "created" event.
     */
    public function created(Appointment $appointment): void
    {
        // If appointment is created with Confirmed status, create conversation
        if ($appointment->status_id === AppointmentStatus::Confirmed->value) {
            $this->chatService->createConversation($appointment);
        }
    }

    /**
     * Send confirmation email to customer
     */
    protected function sendConfirmationEmail(Appointment $appointment): void
    {
        if ($appointment->customer && $appointment->customer->email) {
            Mail::to($appointment->customer->email)
                ->send(new AppointmentConfirmedMail($appointment));
        }
    }

    /**
     * Send rejection email to customer
     */
    protected function sendRejectionEmail(Appointment $appointment): void
    {
        if ($appointment->customer && $appointment->customer->email) {
            Mail::to($appointment->customer->email)
                ->send(new AppointmentRejectedMail($appointment));
        }
    }
}
