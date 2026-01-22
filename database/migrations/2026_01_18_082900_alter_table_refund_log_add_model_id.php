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
         Schema::table('refund_logs', function (Blueprint $table) {
            $table->string('model_type')->nullable();
             $table->unsignedBigInteger('model_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
         Schema::table('refund_logs', function (Blueprint $table) {
            $table->dropColumn('model_type');
             $table->dropColumn('model_id');
        });
    }
};
