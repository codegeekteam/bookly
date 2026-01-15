<?php

namespace Database\Factories;

use App\Models\GiftCard;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class GiftCardFactory extends Factory
{
    protected $model = GiftCard::class;

    public function definition(): array
    {

        $users = \App\Models\User::pluck('id')->toArray();
        $appointments = \App\Models\Appointment::pluck('id')->toArray();
        $customers = \App\Models\Customer::pluck('id')->toArray();

        return [
            'code' => $this->faker->lexify('BKLY-????'),
            'user_id' => $this->faker->randomElement($users),
            'amount' => $this->faker->numberBetween(1, 999),
            'recipient_name' => $this->faker->name(),
            'recipient_email' => $this->faker->unique()->safeEmail(),
            'recipient_phone_number' => $this->faker->phoneNumber(),
            'is_used' => $this->faker->boolean(),
            'used_by' => $this->faker->randomElement($customers),
            'appointment_id' => $this->faker->randomElement($appointments),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
