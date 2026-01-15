<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaign_provider', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\CustomerCampaign::class, 'campaign_id');
            $table->foreignIdFor(\App\Models\ServiceProvider::class, 'provider_id');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_provider');
    }
};
