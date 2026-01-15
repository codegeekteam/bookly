<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Models\DeferredPayout;
use App\Models\PayoutSetting;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SimulatePayoutForAppointment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:simulate-payout {appointment_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Simulate payout creation for a specific appointment (for testing purposes)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $appointmentId = $this->argument('appointment_id');

        $appointment = Appointment::with([
            'depositPaymentMethod',
            'remainingPaymentMethod',
            'paymentMethod',
            'serviceProvider'
        ])->find($appointmentId);

        if (!$appointment) {
            $this->error("Appointment with ID {$appointmentId} not found.");
            return Command::FAILURE;
        }

        $this->info("Simulating payout for Appointment #{$appointmentId}");
        $this->info("Service Provider: {$appointment->serviceProvider->name}");
        $this->line('');

        $settings = PayoutSetting::get();
        $completedAt = now();
        $availableAt = now(); // Make immediately available for testing

        $createdRecords = [];

        // Handle deposit payment if exists and paid via card
        if ($appointment->deposit_amount > 0 &&
            $appointment->deposit_payment_status === 'paid' &&
            $appointment->deposit_payment_method_id) {

            $paymentMethod = $appointment->depositPaymentMethod;

            if ($paymentMethod && strtolower($paymentMethod->name) !== 'cash') {
                $deferredPayout = DeferredPayout::create([
                    'appointment_id' => $appointment->id,
                    'service_provider_id' => $appointment->service_provider_id,
                    'amount' => $appointment->deposit_amount,
                    'payment_type' => 'deposit',
                    'payment_method_id' => $appointment->deposit_payment_method_id,
                    'completed_at' => $completedAt,
                    'available_at' => $availableAt,
                    'status' => 'pending',
                ]);

                $createdRecords[] = [
                    'ID' => $deferredPayout->id,
                    'Type' => 'Deposit',
                    'Amount' => number_format($deferredPayout->amount, 2) . ' SAR',
                    'Payment Method' => $paymentMethod->name,
                    'Available At' => $availableAt->toDateTimeString(),
                ];
            } else {
                $this->warn("Deposit payment is via cash - skipped");
            }
        }

        // Handle remaining payment if exists and paid via card
        if ($appointment->remaining_amount > 0 &&
            $appointment->remaining_payment_status === 'paid' &&
            $appointment->remaining_payment_method_id) {

            $paymentMethod = $appointment->remainingPaymentMethod;

            if ($paymentMethod && strtolower($paymentMethod->name) !== 'cash') {
                $deferredPayout = DeferredPayout::create([
                    'appointment_id' => $appointment->id,
                    'service_provider_id' => $appointment->service_provider_id,
                    'amount' => $appointment->remaining_amount,
                    'payment_type' => 'remaining',
                    'payment_method_id' => $appointment->remaining_payment_method_id,
                    'completed_at' => $completedAt,
                    'available_at' => $availableAt,
                    'status' => 'pending',
                ]);

                $createdRecords[] = [
                    'ID' => $deferredPayout->id,
                    'Type' => 'Remaining',
                    'Amount' => number_format($deferredPayout->amount, 2) . ' SAR',
                    'Payment Method' => $paymentMethod->name,
                    'Available At' => $availableAt->toDateTimeString(),
                ];
            } else {
                $this->warn("Remaining payment is via cash - skipped");
            }
        }

        // Handle case where appointment doesn't have separate deposit/remaining
        if ($appointment->deposit_amount == 0 &&
            $appointment->remaining_amount == 0 &&
            $appointment->total_payed > 0 &&
            $appointment->payment_method_id) {

            $paymentMethod = $appointment->paymentMethod;

            if ($paymentMethod && strtolower($paymentMethod->name) !== 'cash') {
                $deferredPayout = DeferredPayout::create([
                    'appointment_id' => $appointment->id,
                    'service_provider_id' => $appointment->service_provider_id,
                    'amount' => $appointment->total_payed,
                    'payment_type' => 'remaining',
                    'payment_method_id' => $appointment->payment_method_id,
                    'completed_at' => $completedAt,
                    'available_at' => $availableAt,
                    'status' => 'pending',
                ]);

                $createdRecords[] = [
                    'ID' => $deferredPayout->id,
                    'Type' => 'Full Payment',
                    'Amount' => number_format($deferredPayout->amount, 2) . ' SAR',
                    'Payment Method' => $paymentMethod->name,
                    'Available At' => $availableAt->toDateTimeString(),
                ];
            } else {
                $this->warn("Payment is via cash - skipped");
            }
        }

        if (empty($createdRecords)) {
            $this->warn("No deferred payout records created. Appointment may not have card payments.");
            return Command::SUCCESS;
        }

        $this->line('');
        $this->info("✓ Successfully created " . count($createdRecords) . " deferred payout record(s):");
        $this->table(
            ['ID', 'Type', 'Amount', 'Payment Method', 'Available At'],
            $createdRecords
        );

        $this->line('');
        $this->info("Note: Amount is immediately available (available_at = now) for testing purposes.");
        $this->info("In production, holding period is {$settings->holding_period_days} days.");

        return Command::SUCCESS;
    }
}
