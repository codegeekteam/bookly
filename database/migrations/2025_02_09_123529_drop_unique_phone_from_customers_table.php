<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;
return new class extends Migration {
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropUnique(['phone_number']);
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            //
        });
    }
};
