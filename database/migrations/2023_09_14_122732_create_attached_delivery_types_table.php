<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attached_delivery_types', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\AttachedService::class, 'attached_service_id');
            $table->foreignIdFor(\App\Models\DeliveryType::class, 'delivery_type_id');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attached_delivery_types');
    }
};
