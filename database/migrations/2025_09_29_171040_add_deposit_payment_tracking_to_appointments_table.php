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
        Schema::table('appointments', function (Blueprint $table) {
            // Deposit tracking fields
            $table->decimal('deposit_amount', 10, 2)->nullable()->after('amount_due');
            $table->enum('deposit_payment_status', ['pending', 'paid', 'failed'])->nullable()->after('deposit_amount');
            $table->foreignId('deposit_payment_method_id')->nullable()->after('deposit_payment_status')
                ->constrained('payment_methods')->onDelete('set null');

            // Remaining amount tracking fields
            $table->decimal('remaining_amount', 10, 2)->nullable()->after('deposit_payment_method_id');
            $table->enum('remaining_payment_status', ['pending', 'paid', 'failed'])->nullable()->after('remaining_amount');
            $table->foreignId('remaining_payment_method_id')->nullable()->after('remaining_payment_status')
                ->constrained('payment_methods')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropForeign(['deposit_payment_method_id']);
            $table->dropForeign(['remaining_payment_method_id']);

            $table->dropColumn([
                'deposit_amount',
                'deposit_payment_status',
                'deposit_payment_method_id',
                'remaining_amount',
                'remaining_payment_status',
                'remaining_payment_method_id'
            ]);
        });
    }
};