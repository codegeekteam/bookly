<?php

namespace App\Enums;

enum AppointmentStatus: int
{
    //'pending', 'confirmed', 'cancelled', 'completed', reschedule_request, payment_request
    case Pending = 1;
    case Confirmed = 2;
    case Rejected = 3;
    case Cancelled = 4;
    case Completed = 5;
    case RescheduleRequest = 6;
    case PaymentRequest = 7;

}
