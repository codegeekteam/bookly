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
        // attached_services
        Schema::table('attached_services', function (Blueprint $table) {
            $table->index('service_id', 'idx_as_service_id');
            $table->index('service_provider_id', 'idx_as_provider_id');
            $table->index('price', 'idx_as_price');

            $table->index(
                ['service_id', 'service_provider_id'],
                'idx_as_service_provider'
            );
        });

             // service_providers
        Schema::table('service_providers', function (Blueprint $table) {
            $table->index(
                ['is_active', 'is_blocked', 'deleted_at'],
                'idx_sp_status'
            );

            $table->index('name', 'idx_sp_name');
        });

           // operational_hours
        Schema::table('operational_hours', function (Blueprint $table) {
            $table->index(
                ['service_id', 'day_of_week'],
                'idx_oh_service_day'
            );

            $table->index(
                ['service_provider_id', 'service_id', 'day_of_week'],
                'idx_oh_provider_service_day'
            );
        });

          // services
        Schema::table('services', function (Blueprint $table) {
            $table->index('is_active', 'idx_services_active');
        });

            // promo_codes
        Schema::table('promo_codes', function (Blueprint $table) {
            $table->index(
                ['start_date', 'end_date'],
                'idx_promo_dates'
            );

            // Uncomment if promo_codes has service_id
            /*
            $table->index(
                ['service_id', 'start_date', 'end_date'],
                'idx_promo_service_dates'
            );
            */
        });

          // Pivot table (adjust table name if different)
        Schema::table('attached_delivery_types', function (Blueprint $table) {
            $table->index(
                ['attached_service_id', 'delivery_type_id'],
                'idx_asdt_attached_delivery'
            );

            $table->index(
                'delivery_type_id',
                'idx_asdt_delivery_type'
            );
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->index('service_provider_id');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attached_services', function (Blueprint $table) {
            $table->dropIndex('idx_as_service_id');
            $table->dropIndex('idx_as_provider_id');
            $table->dropIndex('idx_as_price');
            $table->dropIndex('idx_as_service_provider');
        });

        Schema::table('service_providers', function (Blueprint $table) {
            $table->dropIndex('idx_sp_status');
            $table->dropIndex('idx_sp_name');
        });

        Schema::table('operational_hours', function (Blueprint $table) {
            $table->dropIndex('idx_oh_service_day');
            $table->dropIndex('idx_oh_provider_service_day');
        });

        Schema::table('services', function (Blueprint $table) {
            $table->dropIndex('idx_services_active');
        });

        Schema::table('promo_codes', function (Blueprint $table) {
            $table->dropIndex('idx_promo_dates');
            // $table->dropIndex('idx_promo_service_dates');
        });

        Schema::table('attached_service_delivery_type', function (Blueprint $table) {
            $table->dropIndex('idx_asdt_attached_delivery');
            $table->dropIndex('idx_asdt_delivery_type');
        });

    }
};
