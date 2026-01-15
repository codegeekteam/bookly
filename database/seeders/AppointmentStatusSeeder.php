<?php

namespace Database\Seeders;

use App\Models\AppointmentStatus;
use Illuminate\Database\Seeder;

class AppointmentStatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = ['pending', 'confirmed', 'rejected', 'cancelled', 'completed', 'reschedule_request', 'payment_request'];

        foreach ($statuses as $status) {
            // AppointmentStatus::factory()->create([
            //     'title' => $status,
            // ]);
            AppointmentStatus::firstOrCreate([
                'title' => $status,
            ]);
        }
    }
}
