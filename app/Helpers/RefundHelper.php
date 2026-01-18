<?php

namespace App\Helpers;

use App\Models\GiftCard;
use App\Models\Appointment;
use App\Models\Subscription;

class RefundHelper
{
    public function getMorphClassFromType($type)
    {
        return match ($type) {
            'appointment' => Appointment::class,
            'giftCard'    => GiftCard::class,
            'subscription'=> Subscription::class,
            default       => null
        };
    }
}
