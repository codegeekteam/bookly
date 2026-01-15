<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (User::where('email', 'admin@admin.com')->doesntExist()) {
            User::factory()->create([
                'name' => 'Admin',
                'email' => 'admin@admin.com',
                'password' => bcrypt('password'),
            ]);
        }

        $this->call([
            ProviderTypeSeeder::class,
            DeliveryTypeSeeder::class,
            BookingTypeSeeder::class,
            AppointmentStatusSeeder::class,
            DiscountTypeSeeder::class,
            PaymentMethodSeeder::class,
        ]);
    }
}
