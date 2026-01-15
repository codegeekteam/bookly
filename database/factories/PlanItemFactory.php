<?php

namespace Database\Factories;

use App\Models\PlanItem;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class PlanItemFactory extends Factory
{
    protected $model = PlanItem::class;

    public function definition(): array
    {
        return [
            'title' => [
                'en' => $this->faker->sentence(3),
                'ar' => $this->faker->sentence(3),
            ],
            'plan_id' => $this->faker->randomNumber(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
