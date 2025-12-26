<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\AppointmentStatus;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class AppointmentFactory extends Factory
{
    protected $model = Appointment::class;

    public function definition(): array
    {

        $customers = \App\Models\Customer::pluck('id')->toArray();
        $service_providers = \App\Models\ServiceProvider::pluck('id')->toArray();
        $delivery_types = \App\Models\DeliveryType::pluck('id')->toArray();
        $appointment_statuses = AppointmentStatus::pluck('id')->toArray();

        return [
            'customer_id' => $this->faker->randomElement($customers),
            'service_provider_id' => $this->faker->randomElement($service_providers),
//            'delivery_type_id' => $this->faker->randomElement($delivery_types),
            'status_id' => $this->faker->randomElement($appointment_statuses),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
