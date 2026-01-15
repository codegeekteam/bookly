<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class RewardsSettings extends Settings
{
    public float $referral_bonus;

    public  int $riyals_per_point;


    public static function group(): string
    {
        return 'rewards';
    }
}
