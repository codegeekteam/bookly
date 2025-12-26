<?php

namespace Database\Seeders;

use App\Models\DiscountType;
use Illuminate\Database\Seeder;

class DiscountTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $discount_types = ['fixed', 'percentage'];

        foreach ($discount_types as $type) {
            DiscountType::create([
                'title' => $type,
            ]);
        }
    }
}
