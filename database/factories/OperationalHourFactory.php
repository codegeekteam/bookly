<?php

namespace Database\Factories;

use App\Models\OperationalHour;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class OperationalHourFactory extends Factory
{
    protected $model = OperationalHour::class;

    public function definition(): array
    {
        return [
            'service_id' => $this->faker->word(),
            'service_provider_id' => $this->faker->word(),
            'day_of_week' => $this->faker->word(),
            'start_time' => $this->faker->time(),
            'end_time' => $this->faker->time(),
            'duration_in_minutes' => $this->faker->numberBetween(1, 10),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
