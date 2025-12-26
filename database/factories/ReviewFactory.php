<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\Customer;
use App\Models\Review;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class ReviewFactory extends Factory
{
    protected $model = Review::class;

    public function definition(): array
    {
        $customers = Customer::pluck('id')->toArray();
        $services = Service::pluck('id')->toArray();
        $service_providers = Service::pluck('id')->toArray();
        $appointments = Appointment::pluck('id')->toArray();

        return [
            'rate' => $this->faker->numberBetween(0, 5),
            'comment' => $this->faker->word(),
            'customer_id' => $this->faker->randomElement($customers),
            'service_id' => $this->faker->randomElement($services),
            'service_provider_id' => $this->faker->randomElement($service_providers),
            'appointment_id' => $this->faker->randomElement($appointments),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
