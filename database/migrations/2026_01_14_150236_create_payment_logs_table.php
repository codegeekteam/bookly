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
        Schema::create('payment_logs', function (Blueprint $table) {
            $table->id();
            $table->string('response_code')->nullable();
            $table->string('status')->nullable();
            $table->string('merchant_reference')->nullable();
            $table->integer('amount')->nullable();
            $table->string('currency')->nullable();
            $table->string('appointment_id')->nullable();
            $table->string('fort_id')->nullable();
            $table->json('response')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_logs');
    }
};
