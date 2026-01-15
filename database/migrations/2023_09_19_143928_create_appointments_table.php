<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->time('time_from');
            $table->time('time_to');
            $table->boolean('on_hold')->default(false);
            $table->string('promo_code')->nullable();
            $table->longText('comment')->nullable();
            $table->integer('attached_service_id')->nullable();
            $table->integer('customer_id')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
