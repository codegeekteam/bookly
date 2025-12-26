<?php

namespace Database\Factories;

use App\Models\DiscountType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PromoCode>
 */
class PromoCodeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $discount_types = DiscountType::all()->pluck('id')->toArray();

        return [
            'code' => $this->faker->word(),
            'maximum_redeems' => $this->faker->numberBetween(1, 100),
            'discount_type_id' => $this->faker->randomElement($discount_types),
            'discount_amount' => $this->faker->numberBetween(1, 100),
            'maximum_discount' => $this->faker->numberBetween(1, 100),
            'start_date' => $this->faker->dateTimeBetween('-1 week', '+1 week'),
            'end_date' => $this->faker->dateTimeBetween('-1 week', '+1 week'),
            'is_for_services' => $this->faker->boolean(),
            'is_for_plans' => $this->faker->boolean(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];

    }
}
