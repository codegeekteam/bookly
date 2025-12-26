<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\DiscountType;
return new class extends Migration {
    public function up(): void
    {
        Schema::table('loyalty_discount_customers', function (Blueprint $table) {
            $table->foreignIdFor(DiscountType::class, 'discount_type_id');
        });
    }

    public function down(): void
    {
        Schema::table('loyalty_discount_customers', function (Blueprint $table) {
            $table->dropForeign(['discount_type_id']);
            // Dropping the parent_id and likes columns
            $table->dropColumn(['discount_type_id']);
        });
    }
};
