<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->decimal('total_payed', 10, 2)->default(0);
            $table->decimal('wallet_amount', 10, 2)->default(0);
            $table->decimal('card_amount', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn('total_payed');
            $table->dropColumn('wallet_amount');
            $table->dropColumn('card_amount');
            $table->dropColumn('discount');
        });
    }
};
