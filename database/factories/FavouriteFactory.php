<?php

namespace Database\Factories;

use App\Models\Favourite;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class FavouriteFactory extends Factory
{
    protected $model = Favourite::class;

    public function definition(): array
    {
        $customers = \App\Models\Customer::pluck('id')->toArray();
        $services = \App\Models\Service::pluck('id')->toArray();

        return [
            'customer_id' => $this->faker->randomElement($customers),
            'service_id' => $this->faker->randomElement($services),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
