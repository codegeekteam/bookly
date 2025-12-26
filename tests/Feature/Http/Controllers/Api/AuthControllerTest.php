<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Customer;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    public function test_can_delete_account()
    {
        $customer = Customer::factory()->create();
        $this->actingAs($customer->user);
        $response = $this->deleteJson('api/user', ['access_type' => 'customer']);
        $response->assertOk();
        $this->assertSoftDeleted('customers', ['id' => $customer->id]);
        $this->assertDatabaseHas('customers', ['phone_number' => $customer->phone_number.'_deleted_'.$customer->id]);
        $this->assertDatabaseMissing('users', ['id' => $customer->user->id]);
    }

    public function test_can_create_again_after_deleted()
    {
        $customer = Customer::factory()->create();
        $this->actingAs($customer->user);
        $this->deleteJson('api/user', ['access_type' => 'customer']);
        $this->refreshApplication();
        $response = $this->postJson('api/login', ['phone_number' => $customer->phone_number, 'access_type' => 'customer']);
        $response->assertOk();
        $response = $this->postJson('api/verify-otp', ['phone_number' => $customer->phone_number, 'otp' => '123456', 'access_type' => 'customer']);
        $response->assertOk();
        $customer = Customer::where('phone_number', $customer->phone_number)->first();
        $this->assertDatabaseHas('customers', ['phone_number' => $customer->phone_number]);
        $this->assertDatabaseHas('users', ['id' => $customer->user->id]);
    }
}
