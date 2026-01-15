<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->decimal('latitude', 10, 7)->change();
            $table->decimal('longitude', 10, 7)->change();

        });
    }

    public function down(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->decimal('latitude', 15, 15)->change();
            $table->decimal('longitude', 15, 15)->change();
        });
    }
};
