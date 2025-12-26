<?php

namespace Database\Factories;

use App\Models\ServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class ServiceProviderFactory extends Factory
{
    protected $model = ServiceProvider::class;

    public function definition(): array
    {
        $provider_types = \App\Models\ProviderType::pluck('id')->toArray();

        return [
            'name' => $this->faker->word(),
            'is_blocked' => $this->faker->boolean(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone_number' => $this->faker->phoneNumber(),
            'biography' => $this->faker->text(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'is_active' => $this->faker->boolean(),
            'max_appointments_per_day' => $this->faker->numberBetween(10, 999),
            'deposit_type' => $this->faker->randomElement(['fixed', 'percentage']),
            'deposit_amount' => $this->faker->numberBetween(10, 999),
            'provider_type_id' => $this->faker->randomElement($provider_types),

        ];
    }
}
