<?php

namespace Database\Seeders;

use App\Models\GiftCard;
use Illuminate\Database\Seeder;

class GiftCardSeeder extends Seeder
{
    public function run(): void
    {

        $giftCardRecipients = [
            [
                'recipient_name' => 'عبد الرحمن محمد',
                'recipient_email' => 'abdulrahman.mohammed@gmail.com',
                'recipient_phone_number' => '966500000001',
            ],
            [
                'recipient_name' => 'فاطمة خالد',
                'recipient_email' => 'fatima.khaled@gmail.com',
                'recipient_phone_number' => '966500000002',
            ],
            [
                'recipient_name' => 'عبد الله أحمد',
                'recipient_email' => 'abdullah.ahmed@gmail.com',
                'recipient_phone_number' => '966500000003',
            ],
            [
                'recipient_name' => 'سارة علي',
                'recipient_email' => 'sara.ali@gmail.com',
                'recipient_phone_number' => '966500000004',
            ],
            [
                'recipient_name' => 'يوسف نور',
                'recipient_email' => 'youssef.noor@gmail.com',
                'recipient_phone_number' => '966500000005',
            ],
            [
                'recipient_name' => 'لمى عبد العزيز',
                'recipient_email' => 'lama.abdulaziz@gmail.com',
                'recipient_phone_number' => '966500000006',
            ],
            [
                'recipient_name' => 'أحمد عبد العزيز',
                'recipient_email' => 'ahmed.abdulaziz@gmail.com',
                'recipient_phone_number' => '966500000007',
            ],
            [
                'recipient_name' => 'مريم علي',
                'recipient_email' => 'maryam.ali@gmail.com',
                'recipient_phone_number' => '966500000008',
            ],
            [
                'recipient_name' => 'خالد نور',
                'recipient_email' => 'khaled.noor@gmail.com',
                'recipient_phone_number' => '966500000009',
            ],
            [
                'recipient_name' => 'نورة عبد الرحمن',
                'recipient_email' => 'noura.abdulrahman@gmail.com',
                'recipient_phone_number' => '966500000010',
            ],
        ];

        foreach ($giftCardRecipients as $recipient) {
            GiftCard::factory()->create([
                'recipient_name' => $recipient['recipient_name'],
                'recipient_email' => $recipient['recipient_email'],
                'recipient_phone_number' => $recipient['recipient_phone_number'],
            ]);
        }
    }
}
