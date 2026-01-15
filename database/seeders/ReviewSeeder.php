<?php

namespace Database\Seeders;

use App\Models\Review;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{




    public function run(): void
    {
        $comments = [
            'Great service',
            'I loved it',
            'I will come back again',
            'I will recommend it to my friends',
            'I will recommend it to my family',
            'I will recommend it to my colleagues',
            'I will recommend it to my neighbors',
        ];

     foreach ($comments as $comment) {
            Review::factory()->create([
                'rate' => rand(1, 5),
                'comment' => $comment,
            ]);
        }

    }
}
