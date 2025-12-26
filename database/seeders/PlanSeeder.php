<?php

namespace Database\Seeders;

use App\Models\Plan;
use App\Models\PlanItem;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'title' => [
                    'ar' => 'خطة الاشتراك الأساسية',
                    'en' => 'Basic Subscription Plan',
                ],
                'price' => 500,
                'description' => [
                    'ar' => 'هذه الخطة الأساسية تتيح لمقدمي الخدمات الوصول إلى العملاء وتقديم خدماتهم بسهولة وفعالية.',
                    'en' => 'This basic plan allows service providers to access clients and deliver their services with ease and efficiency.',
                ],
                'subplans' => [
                    [
                        'title' => [
                            'ar' => 'اشتراك فضي.',
                            'en' => 'A silver subscription',
                        ],
                    ],
                    [
                        'title' => [
                            'ar' => 'اشتراك برونزي.',
                            'en' => 'A bronze subscription',
                        ],
                    ],
                ],
            ],
            [
                'title' => [
                    'ar' => 'خطة الاشتراك المتقدمة',
                    'en' => 'Advanced Subscription Plan',
                ],
                'price' => 1000,
                'description' => [
                    'ar' => 'تتيح هذه الخطة المتقدمة لمقدمي الخدمات الوصول إلى ميزات إضافية مثل الترويج والإحصائيات المتقدمة.',
                    'en' => 'This advanced plan allows service providers to access additional features such as promotion and advanced analytics.',
                ],
                'subplans' => [
                    [
                        'title' => [
                            'ar' => 'اشتراك فضي.',
                            'en' => 'A silver subscription',
                        ],
                    ],
                    [
                        'title' => [
                            'ar' => 'اشتراك برونزي.',
                            'en' => 'A bronze subscription',
                        ],
                    ],
                ],
            ],
            [
                'title' => [
                    'ar' => 'خطة الاشتراك الذهبية',
                    'en' => 'Gold Subscription Plan',
                ],
                'price' => 1500,
                'description' => [
                    'ar' => 'توفر هذه الخطة الذهبية أعلى مستويات الاشتراك مع ميزات متقدمة ودعم فني متميز.',
                    'en' => 'This gold plan provides the highest levels of subscription with advanced features and premium technical support.',
                ],
                'subplans' => [
                    [
                        'title' => [
                            'ar' => 'اشتراك فضي.',
                            'en' => 'A silver subscription',
                        ],
                    ],
                    [
                        'title' => [
                            'ar' => 'اشتراك برونزي.',
                            'en' => 'A bronze subscription',
                        ],
                    ],
                ],
            ],
            [
                'title' => [
                    'ar' => 'خطة الاشتراك الفضية',
                    'en' => 'Silver Subscription Plan',
                ],
                'price' => 750,
                'description' => [
                    'ar' => 'توفر هذه الخطة الفضية ميزات متوسطة بسعر مناسب لمقدمي الخدمات.',
                    'en' => 'This silver plan offers moderate features at an affordable price for service providers.',
                ],
                'subplans' => [
                    [
                        'title' => [
                            'ar' => 'اشتراك فضي.',
                            'en' => 'A silver subscription',
                        ],
                    ],
                    [
                        'title' => [
                            'ar' => 'اشتراك برونزي.',
                            'en' => 'A bronze subscription',
                        ],
                    ],
                ],
            ],
            [
                'title' => [
                    'ar' => 'خطة الاشتراك المميزة',
                    'en' => 'Premium Subscription Plan',
                ],
                'price' => 1200,
                'description' => [
                    'ar' => 'هذه الخطة المميزة توفر ميزات متقدمة بسعر معقول للمقدمين.',
                    'en' => 'This premium plan offers advanced features at a reasonable price for providers.',
                ],
                'subplans' => [
                    [
                        'title' => [
                            'ar' => 'اشتراك فضي.',
                            'en' => 'A silver subscription',
                        ],
                    ],
                    [
                        'title' => [
                            'ar' => 'اشتراك برونزي.',
                            'en' => 'A bronze subscription',
                        ],
                    ],
                ],
            ],
        ];

        foreach ($plans as $plan) {
            $planModel = Plan::factory()->create(); // plans model has drastically changed

            foreach ($plan['subplans'] as $subplan) {
                PlanItem::create([
                    'plan_id' => $planModel->id,
                    'title' => $subplan['title'],
                ]);
            }
        }
    }
}
