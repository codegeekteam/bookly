<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropColumn('service_id');
            $table->foreignIdFor(\App\Models\AttachedService::class, 'attached_service_id');
        });
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropForeign(['attached_service_id']);
            $table->dropColumn('attached_service_id');
            $table->unsignedBigInteger('service_id');
        });
    }
};
