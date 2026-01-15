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
        Schema::create('promo_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->integer('maximum_redeems');
            $table->foreignIdFor(DiscountType::class, 'discount_type_id');
            $table->float('discount_amount');
            $table->float('maximum_discount');
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->boolean('is_for_services')->default(false);
            $table->boolean('is_for_plans')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promo_codes');
    }
};
