<?php

namespace App\Enums;

class ProviderTypeEnum
{
    const FREELANCER = 1;

    const ENTERPRISE = 2;

    public static $providerTypes = [
        self::FREELANCER => 'freelancer',
        self::ENTERPRISE => 'enterprise',
    ];
}
