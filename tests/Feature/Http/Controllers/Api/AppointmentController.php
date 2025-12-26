<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Address;
use App\Models\Customer;
use App\Models\PaymentMethod;
use App\Models\ProviderType;
use Database\Seeders\AppointmentStatusSeeder;
use Database\Seeders\DeliveryTypeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppointmentController extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_generate_sdk_token(): void
    {
        $customer = Customer::factory()->create();
        $this->actingAs($customer->user);
        $response = $this->getJson('/api/appointments/sdk-token', [
            'device_id' => '123456789',
        ]);
        $response->assertStatus(200)
            ->assertJsonStructure([
                'payment' => [
                    'sdk_token',
                    'response_message',
                    'response_code',
                ],
            ]);
    }

    public function test_customer_can_book_multiple_services(): void
    {
        $this->seed(AppointmentStatusSeeder::class);
        $this->seed(DeliveryTypeSeeder::class);
        $service = \App\Models\Service::factory()->create();
        $service_2 = \App\Models\Service::factory()->create();
        $provider = \App\Models\ServiceProvider::factory()->create();
        $providerType = ProviderType::factory()->create();
        $provider->provider_type_id = $providerType->id;
        $provider->save();
        $provider->services()->attach($service->id, ['price' => 100]);
        $provider->services()->attach($service_2->id, ['price' => 300]);
        $date = '2023-10-24T09:20:41.000000Z';
        $provider->operationalHours()->create([
            'service_id' => $service->id,
            'day_of_week' => date('l', strtotime($date)),
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
            'duration_in_minutes' => 30,
        ]);
        $provider->operationalHours()->create([
            'service_id' => $service_2->id,
            'day_of_week' => date('l', strtotime($date)),
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
            'duration_in_minutes' => 30,
        ]);

        $promo_code = null;
        $comment = null;
        $payment_method_id = PaymentMethod::factory()->create()->id;
        $customer = Customer::factory()->create();
        $address_id = Address::factory()->create([
            'addressable_type' => Customer::class,
            'addressable_id' => $customer->id,
        ])->id;

        $services = [
            [
                'service_id' => $service->id,
                'provider_id' => $provider->id,
                'number_of_beneficiaries' => 1,
                'selected_employee' => null,
                'delivery_type_id' => 1,
                'address_id' => $address_id,
                'time_slot' => '10:00 am',
                'date' => $date,
            ],

            [
                'service_id' => $service_2->id,
                'provider_id' => $provider->id,
                'number_of_beneficiaries' => 3,
                'selected_employee' => null,
                'delivery_type_id' => 2,
                'address_id' => $address_id,
                'time_slot' => '10:30 am',
                'date' => $date,
            ],
        ];
        $this->actingAs($customer->user);
        $response = $this->postJson('/api/appointments', [
            'services' => $services,
            'promo_code' => $promo_code,
            'comment' => $comment,
            'response_code' => '12000',
            'amount' => 400,
            'payment_method_id' => $payment_method_id,
        ]);
        $response->assertStatus(201)
            ->assertJsonStructure([

                'id',
                'date',
                'services' => [
                    '*' => [
                        'name',
                        'price',
                        'service_beneficiaries',
                        'selected_employee',
                        'date',
                        'start_time',
                        'end_time',
                        'delivery_type_id',
                        'delivery_type',
                        'location',
                        'employee',
                    ],
                ],
                'promo_code',
                'discount',
                'payment_method',
                'total' => [
                    'amount',
                    'payment_status',
                    'currency',
                ],
                'provider' => [
                    'id',
                    'name',
                    'image',
                    'type',
                ],
                'has_review',
                'comment',
                'status',
                'created_at',

            ]);
    }
}
