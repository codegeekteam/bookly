<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //$methods = ['Cash', 'Credit Card', 'Debit Card', 'Paypal', 'Stripe', 'Bank Transfer'];
        // Disable foreign key checks
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Truncate the table
        PaymentMethod::truncate();

        // Enable foreign key checks again
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        $methods = ['Card', 'Wallet', 'Card And Wallet', 'Cash'];
        foreach ($methods as $method) {
            PaymentMethod::factory()->create([
                'name' => $method,
                'is_active' => true,
            ]);
        }
    }
}