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
        Schema::create('loyalty_discounts', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(DiscountType::class, 'discount_type_id');
            $table->decimal('discount_amount',10, 2)->default(0);
            $table->decimal('maximum_discount',10, 2)->default(0);
            $table->integer('points')->default(0);
            $table->dateTime('start_date')->nullable();
            $table->dateTime('end_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loyalty_discounts');
    }
};
