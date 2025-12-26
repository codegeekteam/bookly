<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('gift_cards', function (Blueprint $table) {
            $table->enum('payment_status', ['unpaid', 'partially_paid', 'paid'])->default('unpaid');
            $table->foreignId('gift_card_theme_id')->nullable()->constrained('gift_card_themes');
            $table->dateTime('used_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('gift_cards', function (Blueprint $table) {
            $table->dropForeign(['gift_card_theme_id']);
            // Dropping the parent_id and likes columns
            $table->dropColumn(['payment_status','gift_card_theme_id','used_at']);
        });
    }
};
