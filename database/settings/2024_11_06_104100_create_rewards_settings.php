<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('rewards.referral_bonus', 0);
        $this->migrator->add('rewards.loyalty_points_per_riyals', 0);
    }
};
