<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('operational_hours', function (Blueprint $table) {
            $table->float('duration_in_minutes')->default(1);
        });
    }

    public function down(): void
    {
        Schema::table('operational_hours', function (Blueprint $table) {
            $table->dropColumn('duration_in_minutes');
        });
    }
};
