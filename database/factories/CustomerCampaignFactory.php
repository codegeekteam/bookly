<?php

namespace Database\Factories;

use App\Models\CustomerCampaign;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class CustomerCampaignFactory extends Factory
{
    protected $model = CustomerCampaign::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->word(),
            'is_active' => $this->faker->boolean(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
