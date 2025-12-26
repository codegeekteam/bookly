<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\DiscountType;
return new class extends Migration {
    public function up(): void
    {
        Schema::table('loyalty_discount_customers', function (Blueprint $table) {
            $table->decimal('minimum_amount',10, 2)->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('loyalty_discount_customers', function (Blueprint $table) {
            $table->dropColumn(['minimum_amount']);
        });
    }
};
