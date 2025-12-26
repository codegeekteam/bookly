<?php

use App\Models\DiscountType;
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
        Schema::create('loyalty_discount_customers', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\Customer::class, 'customer_id')->constrained()->onDelete('cascade');
            $table->foreignIdFor(\App\Models\LoyaltyDiscount::class, 'loyalty_discount_id')->constrained()->onDelete('cascade');
            $table->decimal('discount_amount',10, 2)->default(0);
            $table->decimal('maximum_discount',10, 2)->default(0);
            $table->integer('points')->default(0);
            $table->boolean('is_used')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loyalty_discount_customers');
    }
};
