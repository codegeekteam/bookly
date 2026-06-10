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
         Schema::table('appointments', function (Blueprint $table) {

            // Main query
            $table->index('service_provider_id');         
            // Eager loaded relationships
            $table->index('customer_id');
            $table->index('promo_code_id');         
            $table->index('status_id');
            $table->index('previous_status_id');
            // Composite index for common query pattern
            $table->index(['service_provider_id', 'id']);
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropIndex(['service_provider_id']);
            $table->dropIndex(['customer_id']);
            $table->dropIndex(['promo_code_id']);  
            $table->dropIndex(['status_id']);
            $table->dropIndex(['previous_status_id']);
            $table->dropIndex(['service_provider_id', 'id']);
        });
    }
};
