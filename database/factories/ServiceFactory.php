<?php

namespace Database\Factories;

use App\Models\DeliveryType;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class ServiceFactory extends Factory
{
    protected $model = Service::class;

    public function definition(): array
    {
        $delivery_type = DeliveryType::all()->pluck('id')->toArray();

        return [
            'title' => [
                'en' => $this->faker->word,
                'ar' => $this->faker->word,
            ],
            'description' => $this->faker->sentence(),
            'is_active' => $this->faker->boolean(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
