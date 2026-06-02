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
        Schema::table('wallets', function (Blueprint $table) {
            $table->double('deposit_balance')->default(0);
            $table->double('refund_balance')->default(0);
            $table->double('goodwill_balance')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wallets', function (Blueprint $table) {
            $table->dropColumn('deposit_balance');
            $table->dropColumn('refund_balance');
            $table->dropColumn('goodwill_balance');
        });
    }
};
