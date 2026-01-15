<?php

namespace App\Services;

use App\Models\Appointment;
use Carbon\Carbon;

class CancellationPolicyService
{
    /**
     * Calculate refund based on cancellation policy
     *
     * @param Appointment $appointment
     * @param bool $isProviderCancelling
     * @return array ['refund_percentage' => int, 'refund_amount' => float]
     */
    public function calculateRefund(Appointment $appointment, bool $isProviderCancelling = false): array
    {
        // If provider is cancelling, refund only the deposit
        if ($isProviderCancelling) {
            $depositPaid = $appointment->deposit_payment_status === 'paid'
                ? (float) $appointment->deposit_amount
                : 0.0;

            return [
                'refund_percentage' => 100,
                'refund_amount' => $depositPaid,
            ];
        }

        // Customer is cancelling - apply policy
        $provider = $appointment->serviceProvider;

        // If cancellation policy is not enabled, always full refund
        if (!$provider->cancellation_enabled) {
            return [
                'refund_percentage' => 100,
                'refund_amount' => (float) $appointment->total_payed,
            ];
        }

        // Get the first service appointment date and time
        $firstService = $appointment->appointmentServices()->first();

        if (!$firstService) {
            // No services found, refund everything
            return [
                'refund_percentage' => 100,
                'refund_amount' => (float) $appointment->total_payed,
            ];
        }

        // Calculate appointment datetime
        $appointmentDateTime = Carbon::parse($firstService->date)
            ->setTimeFromTimeString($firstService->start_time);

        // Calculate hours until appointment
        $hoursUntilAppointment = Carbon::now()->diffInHours($appointmentDateTime, false);

        // If appointment is in the past, no refund
        if ($hoursUntilAppointment < 0) {
            return [
                'refund_percentage' => 0,
                'refund_amount' => 0.0,
            ];
        }

        // Check if cancellation is within the policy window
        if ($hoursUntilAppointment >= $provider->cancellation_hours_before) {
            // Full refund - outside penalty window
            return [
                'refund_percentage' => 100,
                'refund_amount' => (float) $appointment->total_payed,
            ];
        } else {
            // No refund - within penalty window
            return [
                'refund_percentage' => 0,
                'refund_amount' => 0.0,
            ];
        }
    }

    /**
     * Get cancellation policy information for an appointment
     *
     * @param Appointment $appointment
     * @return array
     */
    public function getPolicyInfo(Appointment $appointment): array
    {
        $provider = $appointment->serviceProvider;
        $refundInfo = $this->calculateRefund($appointment);

        return [
            'cancellation_enabled' => (bool) $provider->cancellation_enabled,
            'cancellation_hours_before' => (int) $provider->cancellation_hours_before ?? 0,
            'refund_percentage' => $refundInfo['refund_percentage'],
        ];
    }
}
