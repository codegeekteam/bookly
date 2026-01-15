<?php

use App\Models\PromoCode;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->renameColumn('time_from','start_time');
            $table->renameColumn('time_to','end_time');
            $table->foreignIdFor(PromoCode::class,'promo_code_id')->nullable();
            $table->dropColumn('promo_code');
            $table->integer('number_of_beneficiaries')->default(1);
            $table->dropColumn('customers_count');
            $table->dropColumn('buffer_time_in_minutes');
            $table->dropColumn('employee_id');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->renameColumn('start_time','time_from');
            $table->renameColumn('end_time','time_to');
            $table->dropForeign('appointments_promo_code_id_foreign');
            $table->dropColumn('promo_code_id');
            $table->string('promo_code')->nullable();
            $table->dropColumn('number_of_beneficiaries');
            $table->integer('customers_count')->default(1);
        });
    }

};
