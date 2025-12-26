<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('support.email', 'bookly@bookly.com');
        $this->migrator->add('support.phone_number', '+1 555 555 5555');
        $this->migrator->add('support.whatsapp_phone_number', '+1 555 555 5555');
    }
};
