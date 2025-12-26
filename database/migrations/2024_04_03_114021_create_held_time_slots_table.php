<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('held_time_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id');
            $table->foreignId('service_provider_id');
            $table->date('date');
            $table->dateTime('timeSlot');
            $table->dateTime('expires_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('held_time_slots');
    }
};
