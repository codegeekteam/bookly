<?php

namespace Database\Seeders;

use App\Models\GiftCard;
use App\Models\User;
use Illuminate\Database\Seeder;

class NewGiftCardSeeder extends Seeder
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
            'recipient_name' => 'Fatima Khaled',
            'recipient_email' => 'fatima.khaled@gmail.com',
            'recipient_phone_number' => '966500000002',
        ],
        [
            'recipient_name' => 'عبد الله أحمد',
            'recipient_email' => 'abdullah.ahmed@gmail.com',
            'recipient_phone_number' => '966500000003',
        ],
        [
            'recipient_name' => 'Sara Ali',
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

    $users = User::all();
    foreach ($users as $user)
    {
        $giftCards = GiftCard::factory()->create([
            'recipient_name' => $giftCardRecipients[array_rand($giftCardRecipients)]['recipient_name'],
            'recipient_email' => $giftCardRecipients[array_rand($giftCardRecipients)]['recipient_email'],
            'recipient_phone_number' => $giftCardRecipients[array_rand($giftCardRecipients)]['recipient_phone_number'],
        ]);
        $user->giftCards()->save($giftCards);
    }


}
}
