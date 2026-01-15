<?php

namespace Database\Seeders;

use App\Models\Favourite;
use Illuminate\Database\Seeder;

class FavouriteSeeder extends Seeder
{
    public function run(): void
    {
        Favourite::factory()->count(10)->create();
    }
}
