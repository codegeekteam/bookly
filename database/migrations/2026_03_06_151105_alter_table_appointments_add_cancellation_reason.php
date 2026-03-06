<?php

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
         Schema::table('appointments', function (Blueprint $table) {
            $table->string('admin_cancel_reason')->nullable();
             $table->double('goodwill_amount')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
         Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn('admin_cancel_reason');
             $table->dropColumn('goodwill_amount');
        });
    }
};
