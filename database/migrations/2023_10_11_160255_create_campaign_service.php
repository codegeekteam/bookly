<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaign_service', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\CustomerCampaign::class, 'campaign_id');
            $table->foreignIdFor(\App\Models\Service::class, 'service_id');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_service');
    }
};
