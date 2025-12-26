<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payout_settings', function (Blueprint $table) {
            $table->id();
            $table->integer('holding_period_days')->default(7)->comment('Number of days to hold payment after appointment completion');
            $table->json('payout_days')->comment('Array of weekday numbers (0=Sunday, 6=Saturday) when payouts are processed');
            $table->timestamps();
        });

        // Insert default settings
        DB::table('payout_settings')->insert([
            'holding_period_days' => 7,
            'payout_days' => json_encode([1, 3]), // Monday and Wednesday
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payout_settings');
    }
};
