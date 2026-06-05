<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
         //   $table->index('service_provider_id');
            $table->index(['service_provider_id', 'deleted_at']);
            $table->index(['service_provider_id', 'rate']);
        });

        Schema::table('home_section_providers', function (Blueprint $table) {
            $table->index('home_section_id');
            $table->index('service_provider_id');
            $table->index(['home_section_id', 'service_provider_id']);
        });

        Schema::table('attached_services', function (Blueprint $table) {
         //   $table->index('service_provider_id');
            $table->index(['service_provider_id', 'deleted_at']);
         //   $table->index('service_id');
        });

        Schema::table('operational_hours', function (Blueprint $table) {
           // $table->index('service_provider_id');
            $table->index(['service_provider_id', 'deleted_at']);
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->index('user_id');
            $table->index(['user_id', 'payment_status']);
            $table->index(['user_id', 'payment_status', 'expires_at']);
        });

        Schema::table('service_providers', function (Blueprint $table) {
            $table->index('provider_type_id');
            $table->index('user_id');
           // $table->index('deleted_at');
        });

        Schema::table('provider_types', function (Blueprint $table) {
            $table->index('deleted_at');
        });

        Schema::table('services', function (Blueprint $table) {
            $table->index('deleted_at');
        });
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropIndex(['service_provider_id']);
            $table->dropIndex(['service_provider_id', 'deleted_at']);
            $table->dropIndex(['service_provider_id', 'rate']);
        });

        Schema::table('home_section_providers', function (Blueprint $table) {
            $table->dropIndex(['home_section_id']);
            $table->dropIndex(['service_provider_id']);
            $table->dropIndex(['home_section_id', 'service_provider_id']);
        });

        Schema::table('attached_services', function (Blueprint $table) {
            $table->dropIndex(['service_provider_id']);
            $table->dropIndex(['service_provider_id', 'deleted_at']);
            $table->dropIndex(['service_id']);
        });

        Schema::table('operational_hours', function (Blueprint $table) {
            $table->dropIndex(['service_provider_id']);
            $table->dropIndex(['service_provider_id', 'deleted_at']);
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['user_id', 'payment_status']);
            $table->dropIndex(['user_id', 'payment_status', 'expires_at']);
        });

        Schema::table('service_providers', function (Blueprint $table) {
            $table->dropIndex(['provider_type_id']);
            $table->dropIndex(['user_id']);
            $table->dropIndex(['deleted_at']);
        });

        Schema::table('provider_types', function (Blueprint $table) {
            $table->dropIndex(['deleted_at']);
        });

        Schema::table('services', function (Blueprint $table) {
            $table->dropIndex(['deleted_at']);
        });
    }
};