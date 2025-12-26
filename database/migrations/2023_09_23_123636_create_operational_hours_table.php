<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('operational_hours', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\ServiceProvider::class, 'service_provider_id');
            $table->foreignIdFor(\App\Models\Service::class, 'service_id');
            $table->foreignIdFor(\App\Models\Employee::class, 'employee_id')->nullable();
            $table->string('day_of_week');
            $table->time('start_time');
            $table->time('end_time');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operational_hours');
    }
};
