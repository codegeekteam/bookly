<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            [
                'title' => [
                    'en' => 'Hair Styling',
                    'ar' => 'تصفيف الشعر',
                ],
                'description' => 'Professional hair styling to suit various occasions and preferences.',
            ],
            [
                'title' => [
                    'en' => 'Facial Treatments',
                    'ar' => 'علاجات الوجه',
                ],
                'description' => 'Revitalize your skin with our range of facial treatments targeting different skin concerns.',
            ],
            [
                'title' => [
                    'en' => 'Massage Therapy',
                    'ar' => 'علاج تدليك',
                ],
                'description' => 'Relax your body and mind with our rejuvenating massage therapies.',
            ],
            [
                'title' => [
                    'en' => 'Makeup Services',
                    'ar' => 'خدمات المكياج',
                ],
                'description' => 'Enhance your natural beauty with our professional makeup services for all occasions.',
            ],
        ];

        $categories = Category::all();

        foreach ($services as $service) {
            $new_service = Service::factory()->create([
                'title' => $service['title'],
                'description' => $service['description'],
            ]);

            if ($categories->count() > 0) {
                $new_service->categories()->attach($categories->random(rand(1, 3)));
            }

        }
    }
}
