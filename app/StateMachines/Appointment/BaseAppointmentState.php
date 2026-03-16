<?php

namespace App\StateMachines\Appointment;

use App\Models\Appointment;

abstract class BaseAppointmentState
{
    public function __construct(protected Appointment $appointment)
    {
    }

    public function pending()
    {
        throw new \Exception('Invalid state transition');
    }

    public function confirm()
    {
        throw new \Exception('Invalid state transition');
    }

    public function reject()
    {
        throw new \Exception('Invalid state transition');
    }

    public function cancel()
    {
        throw new \Exception('Invalid state transition');
    }

    public function complete()
    {
        throw new \Exception('Invalid state transition');
    }

    public function rescheduleRequest()
    {
        throw new \Exception('Invalid state transition');
    }

    public function paymentRequest()
    {
        throw new \Exception('Invalid state transition');
    }

    public function cancellationRequest()
    {
        throw new \Exception('Invalid state transition');
    }
 
}
