<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone_number')->nullable();
            $table->boolean('is_blocked')->default(false);
            $table->boolean('is_active')->default(true);
            $table->foreignIdFor(\App\Models\User::class, 'user_id')->nullable();
            $table->foreignIdFor(\App\Models\ServiceProvider::class, 'provider_id')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
