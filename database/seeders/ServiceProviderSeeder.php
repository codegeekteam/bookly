<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\ServiceProvider;
use Illuminate\Database\Seeder;

class ServiceProviderSeeder extends Seeder
{
    public function run(): void
    {

        $serviceProviders = [
            [
                'name' => 'Al-Mamlakah Beauty Center',
                'email' => 'almamlakahbeauty@example.com',
                'phone_number' => '0501234567',
                'biography' => 'We provide a variety of beauty services including makeup, hairstyling, and skincare.',
                'image' => asset('images/serviceProvider/asd1.jpeg'),
                'profile_image' => asset('images/customer/1.png'),
            ],
            [
                'name' => 'Riyadh Glamour Salon',
                'email' => 'riyadhglamour@example.com',
                'phone_number' => '0559876 543',
                'biography' => 'Specializing in professional makeup and hair services for weddings and events.',
                'image' => asset('images/serviceProvider/asd2.jpg'),
                'profile_image' => asset('images/customer/2.jpg'),
            ],
            [
                'name' => 'Jeddah Spa & Wellness',
                'email' => 'jeddahspa@example.com',
                'phone_number' => '0562345678',
                'biography' => 'Indulge in our luxurious spa treatments and experience ultimate relaxation.',
                'image' => asset('images/serviceProvider/asd3.jpeg'),
                'profile_image' => asset('images/customer/3.jpeg'),
            ],
            [
                'name' => 'Dammam Beauty Oasis',
                'email' => 'dammambeauty@example.com',
                'phone_number' => '0548765432',
                'biography' => 'Escape the hustle and bustle and pamper yourself with our skincare and massage therapies.',
                'image' => asset('images/serviceProvider/asd4.jpeg'),
                'profile_image' => asset('images/customer/4.jpeg'),
            ],
        ];

        $services = Service::all();

        foreach ($serviceProviders as $provider) {
            $newServiceProvider = ServiceProvider::factory()->create([
                'name' => $provider['name'],
                'email' => $provider['email'],
                'phone_number' => $provider['phone_number'],
                'biography' => $provider['biography'],
            ]);

            if ($provider['image'] != null) {
                $localImagePath = public_path(str_replace(url('/'), '', $provider['image']));
                $newServiceProvider->addMedia($localImagePath)->preservingOriginal()->toMediaCollection('service_provider_images');
            }

            if ($provider['profile_image'] != null) {
                $localImagePath = public_path(str_replace(url('/'), '', $provider['profile_image']));
                $newServiceProvider->addMedia($localImagePath)->preservingOriginal()->toMediaCollection('service_provider_profile_image');
            }

        }
    }
}
