<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('gift_card_themes', function (Blueprint $table) {
            $table->json('title')->change();
            $table->dropColumn('amount');
        });
    }

    public function down(): void
    {
        Schema::table('gift_card_themes', function (Blueprint $table) {
        });
    }
};
