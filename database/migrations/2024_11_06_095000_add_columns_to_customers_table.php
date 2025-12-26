<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('refer_code')->nullable();
            $table->foreignId('referral_id')->nullable()->constrained('customers')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            //
            $table->dropForeign(['referral_id']);
            // Dropping the parent_id and likes columns
            $table->dropColumn(['refer_code','referral_id']);

        });
    }
};
