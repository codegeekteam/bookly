<?php

use App\Models\Customer;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('favourites', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Customer::class, 'customer_id');
            $table->foreignIdFor(\App\Models\AttachedService::class, 'service_id');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('favourites');
    }
};
