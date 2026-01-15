<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\DiscountType;
return new class extends Migration {
    public function up(): void
    {
        Schema::table('gift_card_themes', function (Blueprint $table) {
            $table->boolean('active')->default(1);
        });
    }

    public function down(): void
    {
        Schema::table('gift_card_themes', function (Blueprint $table) {
            $table->dropColumn(['active']);
        });
    }
};
