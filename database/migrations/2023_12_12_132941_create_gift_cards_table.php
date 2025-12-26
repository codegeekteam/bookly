<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gift_cards', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->foreignIdFor(\App\Models\User::class, 'user_id');
            $table->float('amount');
            $table->string('recipient_name');
            $table->string('recipient_email');
            $table->string('recipient_phone_number');
            $table->boolean('is_used')->default(false);
            $table->foreignIdFor(\App\Models\Customer::class, 'used_by')->nullable();
            $table->foreignIdFor(\App\Models\Appointment::class, 'appointment_id')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gift_cards');
    }
};
