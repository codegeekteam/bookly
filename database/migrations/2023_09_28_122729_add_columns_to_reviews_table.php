<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->foreignIdFor(\App\Models\Appointment::class, 'appointment_id');
            $table->dropColumn('attached_service_id');
            $table->foreignIdFor(\App\Models\Service::class, 'service_id');
            $table->foreignIdFor(\App\Models\ServiceProvider::class, 'service_provider_id');
        });
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropForeign(['appointment_id']);
            $table->dropColumn('appointment_id');
            $table->dropForeign(['service_id']);
            $table->dropColumn('service_id');
            $table->dropForeign(['service_provider_id']);
            $table->dropColumn('service_provider_id');
            $table->foreignIdFor(\App\Models\AttachedService::class, 'attached_service_id');
        });
    }
};
