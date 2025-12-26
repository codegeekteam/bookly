<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DeliveryTypeSeeder extends Seeder
{
    public function run(): void
    {
        $delivery_types = [

            'My Place',

            "Customer's Place",
        ];
        foreach ($delivery_types as $delivery_type) {
            \App\Models\DeliveryType::create([
                'title' => $delivery_type,
            ]);
        }

    }
}
