<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Models\GiftCard;
use App\Models\Subscription;
use App\Services\InvoiceService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SimulatePayfortPayment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payfort:simulate
                            {type : The type of payment (appointment, giftCard, subscription)}
                            {id : The ID of the resource}
                            {--amount= : The amount to pay (optional, uses default if not specified)}
                            {--status=success : The payment status (success, failure)}
                            {--payment-type=full : For appointments: deposit, remaining, or full}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Simulate a Payfort payment response for testing';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (app()->environment('production')) {
            $this->error('❌ Payment simulation is not available in production environment!');
            return 1;
        }

        $type = $this->argument('type');
        $id = $this->argument('id');
        $status = $this->option('status');
        $paymentType = $this->option('payment-type');

        // Validate type
        if (!in_array($type, ['appointment', 'giftCard', 'subscription'])) {
            $this->error("❌ Invalid type. Must be one of: appointment, giftCard, subscription");
            return 1;
        }

        // Find the model
        $model = match ($type) {
            'appointment' => Appointment::find($id),
            'giftCard' => GiftCard::find($id),
            'subscription' => Subscription::find($id),
        };

        if (!$model) {
            $this->error("❌ {$type} with ID {$id} not found!");
            return 1;
        }

        // Display current status
        $this->info("📊 Current Status for {$type} #{$id}:");
        $this->displayCurrentStatus($type, $model);

        // Determine amount
        $amount = $this->option('amount');
        if (!$amount) {
            $amount = $this->calculateDefaultAmount($type, $model, $paymentType);
        }

        // Simulate payment
        if ($status === 'success') {
            $this->simulateSuccessfulPayment($type, $id, $amount, $paymentType);
        } else {
            $this->simulateFailedPayment($type, $id);
        }

        // Refresh and display new status
        $model->refresh();
        $this->newLine();
        $this->info("📊 Updated Status:");
        $this->displayCurrentStatus($type, $model);

        return 0;
    }

    private function displayCurrentStatus($type, $model)
    {
        if ($type === 'appointment') {
            $this->table(
                ['Field', 'Value'],
                [
                    ['Payment Status', $model->payment_status ?? 'N/A'],
                    ['Total', 'SAR ' . number_format($model->total ?? 0, 2)],
                    ['Amount Due', 'SAR ' . number_format($model->amount_due ?? 0, 2)],
                    ['Total Paid', 'SAR ' . number_format($model->total_payed ?? 0, 2)],
                    ['Deposit Amount', $model->deposit_amount ? 'SAR ' . number_format($model->deposit_amount, 2) : 'N/A'],
                    ['Deposit Status', $model->deposit_payment_status ?? 'N/A'],
                    ['Remaining Amount', $model->remaining_amount ? 'SAR ' . number_format($model->remaining_amount, 2) : 'N/A'],
                    ['Remaining Status', $model->remaining_payment_status ?? 'N/A'],
                ]
            );
        } elseif ($type === 'giftCard') {
            $this->table(
                ['Field', 'Value'],
                [
                    ['Is Paid', $model->is_paid ? 'Yes' : 'No'],
                    ['Amount', 'SAR ' . number_format($model->amount ?? 0, 2)],
                ]
            );
        } elseif ($type === 'subscription') {
            $this->table(
                ['Field', 'Value'],
                [
                    ['Payment Status', $model->payment_status ?? 'N/A'],
                    ['Plan Price', 'SAR ' . number_format($model->plan->price ?? 0, 2)],
                    ['Paid Amount', 'SAR ' . number_format($model->paid_amount ?? 0, 2)],
                ]
            );
        }
    }

    private function calculateDefaultAmount($type, $model, $paymentType)
    {
        if ($type === 'appointment') {
            if ($paymentType === 'deposit' && $model->deposit_amount) {
                return $model->deposit_amount;
            } elseif ($paymentType === 'remaining' && $model->remaining_amount) {
                return $model->remaining_amount;
            } else {
                return $model->amount_due ?? 0;
            }
        } elseif ($type === 'giftCard') {
            return $model->amount ?? 0;
        } elseif ($type === 'subscription') {
            return $model->plan->price ?? 0;
        }

        return 0;
    }

    private function simulateSuccessfulPayment($type, $id, $amount, $paymentType)
    {
        $this->info("💳 Simulating successful payment of SAR " . number_format($amount, 2));

        $merchantReference = $type . '_' . $id;
        $payfortAmount = $amount * 100; // Payfort uses smallest currency unit

        // Call the feedback endpoint directly
        $response = $this->processPayfortFeedback('14000', $merchantReference, $payfortAmount);

        if ($paymentType === 'deposit') {
            $this->info("✅ Deposit payment processed successfully!");
        } elseif ($paymentType === 'remaining') {
            $this->info("✅ Remaining payment processed successfully!");
        } else {
            $this->info("✅ Payment processed successfully!");
        }
    }

    private function simulateFailedPayment($type, $id)
    {
        $this->error("❌ Simulating failed payment");

        $merchantReference = $type . '_' . $id;

        // Call with failure response code
        $this->processPayfortFeedback('14001', $merchantReference, 0);

        $this->error("Payment simulation marked as failed!");
    }

    private function processPayfortFeedback($responseCode, $merchantReference, $amount)
    {
        // Call the actual feedback processing logic
        $appointmentService = app(\App\Services\AppointmentService::class);

        return $appointmentService->getPayfortFeedback(
            $responseCode,
            $merchantReference,
            $amount
        );
    }
}