<?php

namespace Database\Seeders;

use App\Enums\AppointmentStatus;
use App\Models\Customer;
use App\Models\Service;
use App\Models\ServiceProvider;
use Illuminate\Database\Seeder;

class AppointmentSeeder extends Seeder
{
    public function run(): void
    {

        $appoimentStatuses = [
            AppointmentStatus::Pending,
            AppointmentStatus::Confirmed,
            AppointmentStatus::Rejected,
            AppointmentStatus::Cancelled,
            AppointmentStatus::Completed,
        ];

        foreach ($appoimentStatuses as $status) {

            $serviceProviderId = ServiceProvider::inRandomOrder()->first()->id;
            $customerId = Customer::inRandomOrder()->first()->id;
            $serviceId = Service::inRandomOrder()->first()->id;

            $appointment = \App\Models\Appointment::factory()->create([
                'service_provider_id' => $serviceProviderId,
                'customer_id' => $customerId,
                'status_id' => $status,
            ]);

            if ($status == AppointmentStatus::Completed) {
                $appointment->services()->attach($serviceId, [
                    'date' => now()->subDays(1)->format('Y-m-d'),
                    'start_time' => now()->addDays(1)->format('H:i:s'),
                    'end_time' => now()->addDays(1)->addHours(1)->format('H:i:s'),
                    'number_of_beneficiaries' => 1,
                    'delivery_type_id' => 1,
                ]);

            } else {
                $appointment->services()->attach($serviceId, [
                    'date' => now()->addDays(1)->format('Y-m-d'),
                    'start_time' => now()->addDays(1)->format('H:i:s'),
                    'end_time' => now()->addDays(1)->addHours(1)->format('H:i:s'),
                    'number_of_beneficiaries' => 1,
                    'delivery_type_id' => 1,
                ]);
            }

        }

    }
}
