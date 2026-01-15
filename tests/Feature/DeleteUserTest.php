<?php

namespace Tests\Feature;

use App\Models\Customer;
use Tests\TestCase;

class DeleteUserTest extends TestCase
{
    public function test_delete_customer()
    {
        $customer = Customer::factory()->create();

        $response = $this->actingAs($customer->user)->deleteJson('/api/user', [
            'access_type' => 'customer',
        ]);

        $response->assertStatus(200);

    }

    public function test_customer_can_register_with_same_phone_number_after_delete()
    {
        $customer = Customer::factory()->create();

        $this->actingAs($customer->user)->deleteJson('/api/user', [
            'access_type' => 'customer',
        ]);

        $new_customer = Customer::factory()->create([
            'phone_number' => $customer->phone_number,
        ]);

        $response = $this->actingAs($new_customer->user)->getJson('/api/user');

        $response->assertStatus(200);

    }
}
