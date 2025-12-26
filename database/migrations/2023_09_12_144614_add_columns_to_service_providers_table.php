<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_providers', function (Blueprint $table) {
            $table->boolean('is_active')->default(true);
            $table->foreignIdFor(\App\Models\ProviderType::class, 'provider_type_id')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('service_providers', function (Blueprint $table) {
            $table->dropColumn('is_active');
            $table->dropColumn('provider_type_id');
        });
    }
};
