<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->foreignIdFor(\App\Models\Service::class, 'service_id')->default(1);
            $table->dropColumn('attached_service_id');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn('service_id');
            $table->foreignIdFor(\App\Models\ServiceProvider::class, 'service_provider_id');
        });
    }
};
