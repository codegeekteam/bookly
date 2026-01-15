<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->foreignIdFor(\App\Models\LoyaltyDiscountCustomer::class, 'loyalty_discount_customer_id')->nullable()->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropForeign(['loyalty_discount_customer_id']);
            // Dropping the parent_id and likes columns
            $table->dropColumn(['loyalty_discount_customer_id']);
        });
    }
};
