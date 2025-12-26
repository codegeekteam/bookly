<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ProviderTypeSeeder extends Seeder
{
    public function run(): void
    {
        $providers = ['freelancer', 'enterprise'];
        foreach ($providers as $provider) {
            \App\Models\ProviderType::factory()->create([
                'title' => $provider,
            ]);
        }
    }
}
