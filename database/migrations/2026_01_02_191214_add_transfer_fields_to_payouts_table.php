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
        Schema::table('payouts', function (Blueprint $table) {
             $table->date('payment_transferred_date')->nullable()->after('status');
            $table->string('transaction_id')->nullable()->after('payment_transferred_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payouts', function (Blueprint $table) {
             $table->dropColumn(['payment_transferred_date', 'transaction_id']);
        });
    }
};
