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
        Schema::create('payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_provider_id')->constrained('service_providers')->onDelete('cascade');
            $table->decimal('total_amount', 10, 2);
            $table->date('due_date')->comment('Date when payout should be transferred');
            $table->enum('status', ['pending', 'transferred', 'cancelled'])->default('pending');
            $table->string('receipt_path')->nullable()->comment('Path to transfer receipt file');
            $table->text('cancellation_note')->nullable()->comment('Reason for cancellation');
            $table->timestamp('transferred_at')->nullable()->comment('When payout was marked as transferred');
            $table->timestamps();

            // Indexes for performance
            $table->index(['service_provider_id', 'status']);
            $table->index('due_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payouts');
    }
};
