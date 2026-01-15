<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        // Rename the old key to the new key
        $this->migrator->rename('rewards.loyalty_points_per_riyals', 'rewards.riyals_per_point');
    }

    public function down(): void
    {
        // Revert back to the old key if rolled back
        $this->migrator->rename('rewards.riyals_per_point', 'rewards.loyalty_points_per_riyals');
    }
};
