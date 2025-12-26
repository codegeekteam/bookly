<?php

namespace Database\Seeders;

use App\Models\BookingType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BookingTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        if (BookingType::count() > 0) {
            Schema::disableForeignKeyConstraints();
            DB::table('booking_types')->truncate();
            Schema::enableForeignKeyConstraints();
        }

        $booking_types = [
            'individual' => ['en' => 'individual', 'ar' => 'فرد'], 'group' => ['en' => 'group', 'ar' => 'مجموعة'],
        ];

        foreach ($booking_types as $key => $value) {
            \App\Models\BookingType::create([
                'slug' => $key,
                'title' => $value,
            ]);

        }

    }
}
