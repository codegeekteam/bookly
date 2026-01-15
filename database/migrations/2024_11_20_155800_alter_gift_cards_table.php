<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('gift_cards', function (Blueprint $table) {
            $table->string('recipient_email')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('gift_cards', function (Blueprint $table) {
        });
    }
};
