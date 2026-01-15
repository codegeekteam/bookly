<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class SupportSettings extends Settings
{
    public string $email;

    public string $phone_number;

    public string $whatsapp_phone_number;

    public string $app_store_link;

    public string $google_play_link;

    public static function group(): string
    {
        return 'support';
    }
}
