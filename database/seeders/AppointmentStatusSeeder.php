<?php

namespace Database\Seeders;

use App\Models\AppointmentStatus;
use Illuminate\Database\Seeder;

class AppointmentStatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = ['pending', 'confirmed', 'rejected', 'cancelled', 'completed'];

        foreach ($statuses as $status) {
            AppointmentStatus::factory()->create([
                'title' => $status,
            ]);
        }
    }
}
