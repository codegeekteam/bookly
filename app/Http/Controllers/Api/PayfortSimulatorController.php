<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\GiftCard;
use App\Models\Subscription;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Payfort Payment Simulator for Testing
 *
 * This controller simulates Payfort payment responses for development/testing.
 * IMPORTANT: This should only be enabled in non-production environments!
 */
class PayfortSimulatorController extends Controller
{
    /**
     * Simulate a successful payment
     *
     * @group Testing - Payfort Simulator
     * @authenticated
     *
     * @bodyParam type string required The type of payment (appointment, giftCard, subscription). Example: appointment
     * @bodyParam id int required The ID of the resource. Example: 123
     * @bodyParam amount float required The amount to pay. Example: 100.00
     * @bodyParam payment_type string The type of payment simulation (deposit, remaining, full). Default: full. Example: deposit
     *
     * @response 200 {
     *   "message": "Payment simulated successfully",
     *   "data": {
     *     "merchant_reference": "appointment_123",
     *     "amount": 10000,
     *     "response_code": "14000",
     *     "payment_status": "paid"
     *   }
     * }
     */
    public function simulateSuccess(Request $request)
    {
        if (app()->environment('production')) {
            return response()->json(['error' => 'Simulator not available in production'], 403);
        }

        $request->validate([
            'type' => 'required|in:appointment,giftCard,subscription',
            'id' => 'required|integer',
            'amount' => 'required|numeric|min:0',
            'payment_type' => 'in:deposit,remaining,full'
        ]);

        $merchantReference = $request->type . '_' . $request->id;
        $amount = $request->amount * 100; // Payfort uses amount * 100

        // If appointment, handle deposit/remaining logic
        if ($request->type === 'appointment' && $request->payment_type) {
            $appointment = Appointment::find($request->id);
            if (!$appointment) {
                return response()->json(['error' => 'Appointment not found'], 404);
            }

            // Determine amount based on payment type
            if ($request->payment_type === 'deposit' && $appointment->deposit_amount) {
                $amount = $appointment->deposit_amount * 100;
            } elseif ($request->payment_type === 'remaining' && $appointment->remaining_amount) {
                $amount = $appointment->remaining_amount * 100;
            }
        }

        // Simulate the Payfort response
        $response = $this->processPayfortResponse(
            response_code: '14000', // Success code
            merchant_reference: $merchantReference,
            amount: $amount
        );

        return response()->json([
            'message' => 'Payment simulated successfully',
            'data' => [
                'merchant_reference' => $merchantReference,
                'amount' => $amount,
                'response_code' => '14000',
                'payment_status' => $response['payment_status'] ?? 'processed'
            ]
        ]);
    }

    /**
     * Simulate a failed payment
     *
     * @group Testing - Payfort Simulator
     * @authenticated
     *
     * @bodyParam type string required The type of payment (appointment, giftCard, subscription). Example: appointment
     * @bodyParam id int required The ID of the resource. Example: 123
     * @bodyParam reason string The failure reason code. Default: 14001. Example: 14001
     *
     * @response 200 {
     *   "message": "Payment failure simulated",
     *   "data": {
     *     "merchant_reference": "appointment_123",
     *     "response_code": "14001",
     *     "status": "failed"
     *   }
     * }
     */
    public function simulateFailure(Request $request)
    {
        if (app()->environment('production')) {
            return response()->json(['error' => 'Simulator not available in production'], 403);
        }

        $request->validate([
            'type' => 'required|in:appointment,giftCard,subscription',
            'id' => 'required|integer',
            'reason' => 'string'
        ]);

        $merchantReference = $request->type . '_' . $request->id;
        $responseCode = $request->reason ?? '14001'; // Default failure code

        return response()->json([
            'message' => 'Payment failure simulated',
            'data' => [
                'merchant_reference' => $merchantReference,
                'response_code' => $responseCode,
                'status' => 'failed'
            ]
        ]);
    }

    /**
     * Simulate Payfort webhook/callback
     *
     * This simulates the actual Payfort feedback endpoint
     *
     * @group Testing - Payfort Simulator
     *
     * @bodyParam response_code string required The Payfort response code. Example: 14000
     * @bodyParam merchant_reference string required The reference (type_id format). Example: appointment_123
     * @bodyParam amount int required The amount in smallest currency unit (fils/cents). Example: 10000
     *
     * @response 200 {
     *   "message": "success"
     * }
     */
    public function simulateCallback(Request $request)
    {
        if (app()->environment('production')) {
            return response()->json(['error' => 'Simulator not available in production'], 403);
        }

        $request->validate([
            'response_code' => 'required|string',
            'merchant_reference' => 'required|string',
            'amount' => 'required|integer'
        ]);

        return $this->processPayfortResponse(
            response_code: $request->response_code,
            merchant_reference: $request->merchant_reference,
            amount: $request->amount
        );
    }

    /**
     * Process the simulated Payfort response
     * This mimics the actual getPayfortFeedback logic
     */
    private function processPayfortResponse($response_code, $merchant_reference, $amount)
    {
        // Normalize amount (Payfort sends multiplied by 100)
        $normalizedAmount = $amount / 100;

        // Split the ID into type and identifier
        $parts = explode('_', $merchant_reference);
        if (count($parts) !== 2) {
            return response()->json(['message' => 'Invalid ID format'], 200);
        }

        [$type, $identifier] = $parts;

        // Resolve model
        $model = match ($type) {
            'appointment' => Appointment::find($identifier),
            'giftCard'    => GiftCard::find($identifier),
            'subscription'=> Subscription::find($identifier),
            default       => null,
        };

        if (!$model) {
            return response()->json(['message' => 'Resource not found'], 200);
        }

        // Check success response_code
        if (substr($response_code, 2) !== '000') {
            return response()->json(['message' => 'Payment failed', 'payment_status' => 'failed'], 200);
        }

        // Appointment logic with new deposit/remaining tracking
        if ($type === 'appointment') {
            $appointment = $model;

            // Determine if this is a deposit or remaining payment
            if ($appointment->deposit_amount && $appointment->deposit_payment_status !== 'paid') {
                // This is a deposit payment
                if ($normalizedAmount >= $appointment->deposit_amount) {
                    $appointment->deposit_payment_status = 'paid';
                    $appointment->card_amount = ($appointment->card_amount ?? 0) + $normalizedAmount;
                    $appointment->total_payed = ($appointment->total_payed ?? 0) + $normalizedAmount;

                    // Update overall status
                    if ($appointment->remaining_amount == 0) {
                        $appointment->payment_status = 'paid';
                    } else {
                        $appointment->payment_status = 'partially_paid';
                    }
                }
            } else {
                // This is a remaining payment or full payment
                $newTotal = $normalizedAmount + ($appointment->total_payed ?? 0);
                $isPaid = $newTotal >= $appointment->amount_due;

                if ($appointment->remaining_amount) {
                    $appointment->remaining_payment_status = $isPaid ? 'paid' : 'pending';
                }

                $appointment->payment_status = $isPaid ? 'paid' : 'partially_paid';
                $appointment->card_amount = ($appointment->card_amount ?? 0) + $normalizedAmount;
                $appointment->total_payed = $newTotal;
            }

            $appointment->save();

            // Generate invoice if payment is complete
            if ($appointment->payment_status === 'paid' && !$appointment->invoice) {
                try {
                    $invoiceService = new InvoiceService();
                    $invoiceService->generateInvoice($appointment);
                } catch (\Exception $e) {
                    Log::error('Failed to generate invoice for appointment ' . $appointment->id . ': ' . $e->getMessage());
                }
            }

            // Send notification if payment is complete
            if ($appointment->payment_status === 'paid') {
                try {
                    $appointment->serviceProvider->user->notify(new \App\Notifications\NewAppointmentNotification($appointment));
                } catch (\Exception $e) {
                    Log::info($e);
                }
            }

            return response()->json([
                'message' => 'success',
                'payment_status' => $appointment->payment_status
            ], 200);
        }

        // GiftCard logic
        if ($type === 'giftCard') {
            $model->update(['is_paid' => true]);
            return response()->json(['message' => 'success'], 200);
        }

        // Subscription logic
        if ($type === 'subscription') {
            $newTotal = $normalizedAmount + $model->paid_amount;
            $isPaid = $newTotal >= $model->plan->price;

            $model->update([
                'payment_status' => $isPaid ? 'paid' : 'partially_paid',
                'paid_amount' => $newTotal,
            ]);

            return response()->json(['message' => 'success'], 200);
        }

        return response()->json(['message' => 'success'], 200);
    }

    /**
     * Get payment status for testing
     *
     * @group Testing - Payfort Simulator
     * @authenticated
     *
     * @urlParam type string required The type of payment (appointment, giftCard, subscription). Example: appointment
     * @urlParam id int required The ID of the resource. Example: 123
     *
     * @response 200 {
     *   "type": "appointment",
     *   "id": 123,
     *   "payment_status": "partially_paid",
     *   "total": 500.00,
     *   "total_payed": 250.00,
     *   "amount_due": 500.00,
     *   "deposit_amount": 150.00,
     *   "deposit_payment_status": "paid",
     *   "remaining_amount": 350.00,
     *   "remaining_payment_status": "pending"
     * }
     */
    public function getPaymentStatus($type, $id)
    {
        if (app()->environment('production')) {
            return response()->json(['error' => 'Simulator not available in production'], 403);
        }

        $model = match ($type) {
            'appointment' => Appointment::find($id),
            'giftCard'    => GiftCard::find($id),
            'subscription'=> Subscription::find($id),
            default       => null,
        };

        if (!$model) {
            return response()->json(['error' => 'Resource not found'], 404);
        }

        $data = [
            'type' => $type,
            'id' => $id,
        ];

        if ($type === 'appointment') {
            $data = array_merge($data, [
                'payment_status' => $model->payment_status,
                'total' => $model->total,
                'total_payed' => $model->total_payed,
                'amount_due' => $model->amount_due,
                'deposit_amount' => $model->deposit_amount,
                'deposit_payment_status' => $model->deposit_payment_status,
                'remaining_amount' => $model->remaining_amount,
                'remaining_payment_status' => $model->remaining_payment_status,
            ]);
        } elseif ($type === 'giftCard') {
            $data['is_paid'] = $model->is_paid;
        } elseif ($type === 'subscription') {
            $data = array_merge($data, [
                'payment_status' => $model->payment_status,
                'paid_amount' => $model->paid_amount,
                'plan_price' => $model->plan->price,
            ]);
        }

        return response()->json($data);
    }
}