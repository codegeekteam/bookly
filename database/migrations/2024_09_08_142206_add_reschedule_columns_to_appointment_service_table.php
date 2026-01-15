<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointment_service', function (Blueprint $table) {
           $table->dateTime('new_start_time')->nullable();
           $table->dateTime('new_end_time')->nullable();
           $table->boolean('accepted_reschedule')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('appointment_service', function (Blueprint $table) {
            $table->dropColumn('new_start_time');
            $table->dropColumn('new_end_time');
            $table->dropColumn('accepted_reschedule');
        });
    }
};
