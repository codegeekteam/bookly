<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('attached_services', function (Blueprint $table) {
            $table->boolean('has_deposit')->default(false);
            $table->decimal('deposit', 8, 2)->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('attached_services', function (Blueprint $table) {
            //
        });
    }
};
