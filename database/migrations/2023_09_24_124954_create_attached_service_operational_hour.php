<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attached_service_operational_hour', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\AttachedService::class, 'attached_service_id');
            $table->foreignIdFor(\App\Models\OperationalHour::class, 'operational_hour_id');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attached_service_operational_hour');
    }
};
