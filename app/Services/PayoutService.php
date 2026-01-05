<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\DeferredPayout;
use App\Models\Payout;
use App\Models\PayoutSetting;
use App\Mail\PayoutTransferredMail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\UploadedFile;

class PayoutService
{
    /**
     * Create deferred payout records when an appointment is completed
     * Only for card payments (not cash)
     */
    public function createDeferredPayoutsForAppointment(Appointment $appointment): void
    {
        $settings = PayoutSetting::get();
        $completedAt = now();
        $availableAt = $settings->calculateAvailableAt($completedAt);

        // Handle deposit payment if exists and paid via card
        if ($appointment->deposit_amount > 0 &&
            $appointment->deposit_payment_status === 'paid' &&
            $appointment->deposit_payment_method_id) {

            $paymentMethod = $appointment->depositPaymentMethod;

            // Only create deferred payout for card payments (not cash)
            if ($paymentMethod && strtolower($paymentMethod->name) !== 'cash') {
                DeferredPayout::create([
                    'appointment_id' => $appointment->id,
                    'service_provider_id' => $appointment->service_provider_id,
                    'amount' => $appointment->deposit_amount,
                    'payment_type' => 'deposit',
                    'payment_method_id' => $appointment->deposit_payment_method_id,
                    'completed_at' => $completedAt,
                    'available_at' => $availableAt,
                    'status' => 'pending',
                ]);
            }
        }

        // Handle remaining payment if exists and paid via card
        if ($appointment->remaining_amount > 0 &&
            $appointment->remaining_payment_status === 'paid' &&
            $appointment->remaining_payment_method_id) {

            $paymentMethod = $appointment->remainingPaymentMethod;

            // Only create deferred payout for card payments (not cash)
            if ($paymentMethod && strtolower($paymentMethod->name) !== 'cash') {
                DeferredPayout::create([
                    'appointment_id' => $appointment->id,
                    'service_provider_id' => $appointment->service_provider_id,
                    'amount' => $appointment->remaining_amount,
                    'payment_type' => 'remaining',
                    'payment_method_id' => $appointment->remaining_payment_method_id,
                    'completed_at' => $completedAt,
                    'available_at' => $availableAt,
                    'status' => 'pending',
                ]);
            }
        }

        // Handle case where appointment doesn't have separate deposit/remaining
        // but has a total payment via card
        if ($appointment->deposit_amount == 0 &&
            $appointment->remaining_amount == 0 &&
            $appointment->total_payed > 0 &&
            $appointment->payment_method_id) {

            $paymentMethod = $appointment->paymentMethod;

            // Only create deferred payout for card payments (not cash)
            if ($paymentMethod && strtolower($paymentMethod->name) !== 'cash') {
                DeferredPayout::create([
                    'appointment_id' => $appointment->id,
                    'service_provider_id' => $appointment->service_provider_id,
                    'amount' => $appointment->total_payed,
                    'payment_type' => 'remaining', // Treat as remaining payment
                    'payment_method_id' => $appointment->payment_method_id,
                    'completed_at' => $completedAt,
                    'available_at' => $availableAt,
                    'status' => 'pending',
                ]);
            }
        }
    }

    /**
     * Group eligible deferred payouts into payout records
     * Called by scheduled command on payout days
     */
    public function groupEligiblePayouts(): array
    {
        $settings = PayoutSetting::get();
        $today = Carbon::today();

        // Check if today is a payout day
        if (!$settings->isPayoutDay($today)) {
            return [
                'success' => false,
                'message' => 'Today is not a payout day',
                'payouts_created' => 0,
            ];
        }

        $payoutsCreated = 0;

        DB::transaction(function () use ($today, &$payoutsCreated) {
            // Get all eligible deferred payouts grouped by provider
            $eligiblePayouts = DeferredPayout::availableForPayout()
                ->with(['serviceProvider', 'appointment'])
                ->get()
                ->groupBy('service_provider_id');

            foreach ($eligiblePayouts as $providerId => $deferredPayouts) {
                $totalAmount = $deferredPayouts->sum('amount');

                // Create the payout record
                $payout = Payout::create([
                    'service_provider_id' => $providerId,
                    'total_amount' => $totalAmount,
                    'due_date' => $today,
                    'status' => 'pending',
                ]);

                // Link all deferred payouts to this payout
                foreach ($deferredPayouts as $deferredPayout) {
                    $deferredPayout->update([
                        'status' => 'grouped',
                        'payout_id' => $payout->id,
                    ]);
                }

                $payoutsCreated++;
            }
        });

        return [
            'success' => true,
            'message' => "Successfully created {$payoutsCreated} payout(s)",
            'payouts_created' => $payoutsCreated,
        ];
    }

    /**
     * Mark a payout as transferred
     */
    public function markAsTransferred(Payout $payout, $transferDate, $transactionId, $receipt = null, ): void
    {
        DB::transaction(function () use ($payout, $transferDate, $transactionId, $receipt) {
            $receiptPath = null;

            if ($receipt) {
                // Handle both UploadedFile object and string path (from Filament)
                if ($receipt instanceof UploadedFile) {
                    $receiptPath = $receipt->store('payout-receipts', 'public');
                } else {
                    $receiptPath = $receipt; // Already stored by Filament
                }        
            }

            $payout->update([
                'status' => 'transferred',
                'transferred_at' => now(),
                'receipt_path' => $receiptPath,
                'payment_transferred_date' => $transferDate, 
                'transaction_id' => $transactionId
            ]);

            // Send email notification to service provider
            $this->sendTransferredEmail($payout);
        });
    }

    /**
     * Cancel a payout
     */
    public function cancelPayout(Payout $payout, string $note): void
    {
        DB::transaction(function () use ($payout, $note) {
            $payout->update([
                'status' => 'cancelled',
                'cancellation_note' => $note,
            ]);

            // Reset deferred payouts back to pending
            $payout->deferredPayouts()->update([
                'status' => 'pending',
                'payout_id' => null,
            ]);
        });
    }

    /**
     * Send email notification when payout is transferred
     */
    protected function sendTransferredEmail(Payout $payout): void
    {
        $payout->load(['serviceProvider.user', 'deferredPayouts.appointment']);

        $email = $payout->serviceProvider->email ?? $payout->serviceProvider->user->email ?? null;

        if ($email) {
            Mail::to($email)->send(new PayoutTransferredMail($payout));
        }
    }

    /**
     * Get deferred payouts summary for a provider
     */
    public function getProviderDeferredSummary(int $providerId): array
    {
        // Not yet available amounts
        $notYetAvailable = DeferredPayout::forProvider($providerId)
            ->notYetAvailable()
            ->with(['appointment', 'paymentMethod'])
            ->get();

        // Available amounts (in pending payouts)
        $available = DeferredPayout::forProvider($providerId)
            ->where('status', 'grouped')
            ->whereHas('payout', function ($query) {
                $query->where('status', 'pending');
            })
            ->with(['appointment', 'paymentMethod', 'payout'])
            ->get();

        return [
            'not_yet_available' => $notYetAvailable,
            'not_yet_available_total' => $notYetAvailable->sum('amount'),
            'available' => $available,
            'available_total' => $available->sum('amount'),
        ];
    }

    /**
     * Get transferred payouts history for a provider
     */
    public function getProviderTransferredHistory(int $providerId)
    {
        return Payout::forProvider($providerId)
            ->transferred()
            ->with(['deferredPayouts.appointment'])
            ->orderBy('transferred_at', 'desc')
            ->get();
    }
}
