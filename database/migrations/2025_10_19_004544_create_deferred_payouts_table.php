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
        Schema::create('deferred_payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->constrained('appointments')->onDelete('cascade');
            $table->foreignId('service_provider_id')->constrained('service_providers')->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->enum('payment_type', ['deposit', 'remaining'])->comment('Type of payment: deposit or remaining balance');
            $table->foreignId('payment_method_id')->constrained('payment_methods')->onDelete('cascade');
            $table->timestamp('completed_at')->comment('When appointment was completed');
            $table->timestamp('available_at')->comment('When amount becomes available for payout (completed_at + holding period)');
            $table->enum('status', ['pending', 'grouped'])->default('pending')->comment('pending: waiting for payout day, grouped: added to payout');
            $table->unsignedBigInteger('payout_id')->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index(['service_provider_id', 'status', 'available_at']);
            $table->index('appointment_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deferred_payouts');
    }
};
