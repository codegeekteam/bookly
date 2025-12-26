<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('deferred_payouts', function (Blueprint $table) {
            $table->foreign('payout_id')
                ->references('id')
                ->on('payouts')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deferred_payouts', function (Blueprint $table) {
            $table->dropForeign(['payout_id']);
        });
    }
};
