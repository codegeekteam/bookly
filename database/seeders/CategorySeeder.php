<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {

        $categories = [
            [
                'title' => [
                    'en' => 'Hair Care',
                    'ar' => 'العناية بالشعر',
                ],
                'image' => asset('images/categories/hair-care.jpg'),
            ],
            [
                'title' => [
                    'en' => 'Skincare',
                    'ar' => 'العناية بالبشرة',
                ],
                'image' => asset('images/categories/skin-care.jpg'),
            ],
            [
                'title' => [
                    'en' => 'Nail Care',
                    'ar' => 'العناية بالأظافر',
                ],
                'image' => asset('images/categories/nail-care.jpg'),
            ],
            [
                'title' => [
                    'en' => 'Makeup',
                    'ar' => 'المكياج',
                ],
                'image' => asset('images/categories/make-up.jpg'),
            ],
            [
                'title' => [
                    'en' => 'Spa Services',
                    'ar' => 'خدمات السبا',
                ],
                'image' => asset('images/categories/spa.jpg'),
            ],
        ];

        foreach ($categories as $category) {
            $newCategory = Category::factory()->create([
                'title' => $category['title'],
            ]);

            if ($category['image']) {
                $localImagePath = public_path(str_replace(url('/'), '', $category['image']));
                $newCategory->addMedia($localImagePath)->preservingOriginal()->toMediaCollection('category_images');
            }

        }
    }
}
