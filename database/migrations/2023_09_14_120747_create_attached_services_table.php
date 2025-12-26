<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attached_services', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\Service::class, 'service_id');
            $table->foreignIdFor(\App\Models\ServiceProvider::class, 'service_provider_id');
            $table->decimal('price')->default(0);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attached_services');
    }
};
