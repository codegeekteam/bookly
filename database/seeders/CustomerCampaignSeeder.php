<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class CustomerCampaignSeeder extends Seeder
{
    public function run(): void
    {

        $customerCampaigns = [
            [
                'title' => 'New Client Discount',
            ],
            [
                'title' => 'Referral Program',
            ],
            [
                'title' => 'Loyalty Points Rewards',
            ],
            [
                'title' => 'Seasonal Promotions (e.g., Summer Specials)',
            ],
            [
                'title' => 'Birthday Discounts',
            ],
            [
                'title' => 'VIP Membership Program',
            ],
            // Add more campaigns as needed
        ];

        foreach ($customerCampaigns as $campaign) {
            \App\Models\CustomerCampaign::factory()->create([
                'title' => $campaign['title'],
            ]);
        }

    }
}
