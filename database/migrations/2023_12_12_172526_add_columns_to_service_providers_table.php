<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_providers', function (Blueprint $table) {
            $table->integer('max_appointments_per_day')->nullable();
            $table->enum('deposit_type', ['fixed', 'percentage'])->default('fixed');
            $table->integer('deposit_amount')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('service_providers', function (Blueprint $table) {
            $table->dropColumn('max_appointments_per_day');
            $table->dropColumn('deposit_type');
            $table->dropColumn('deposit_amount');
        });
    }
};
