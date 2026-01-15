<?php

namespace Database\Factories;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    public function definition(): array
    {
        $users = \App\Models\User::pluck('id')->toArray();
        $providers = \App\Models\ServiceProvider::pluck('id')->toArray();

        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone_number' => $this->faker->phoneNumber(),
            'is_blocked' => $this->faker->boolean(),
            'is_active' => $this->faker->boolean(),
            'user_id' => $this->faker->randomElement($users),
            'provider_id' => $this->faker->randomElement($providers),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
