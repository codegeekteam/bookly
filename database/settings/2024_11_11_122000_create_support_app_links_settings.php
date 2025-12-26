<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('support.app_store_link', 'https://www.apple.com/eg/app-store/');
        $this->migrator->add('support.google_play_link', 'https://play.google.com/store/apps?hl=en');
    }
};
