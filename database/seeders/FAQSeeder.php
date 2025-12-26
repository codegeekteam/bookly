<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class FAQSeeder extends Seeder
{
    public function run(): void
    {
        $faqs = [
            [
                'question' => [
                    'ar' => 'كيف يمكنني حجز موعد؟',
                    'en' => 'How can I book an appointment?',
                ],
                'answer' => [
                    'ar' => 'يمكنك حجز موعد عن طريق تحميل التطبيق الخاص بنا واختيار الخدمة والتاريخ والوقت المفضلين لديك. بالإضافة إلى ذلك ، يمكنك الاتصال بموظف الاستقبال للمساعدة.',
                    'en' => 'You can book an appointment by downloading our app and selecting your preferred service, date, and time. Alternatively, you can call our receptionist for assistance.',
                ],
            ],
            [
                'question' => [
                    'ar' => 'ما هي الخدمات التي تقدمونها؟',
                    'en' => 'What services do you offer?',
                ],
                'answer' => [
                    'ar' => 'نقدم مجموعة واسعة من الخدمات بما في ذلك تصفيف الشعر وصبغه وتلوينه وتدليك الأظافر والمساج وغيرها. يرجى التحقق من تطبيقنا أو موقعنا على الويب للحصول على قائمة كاملة بالخدمات.',
                    'en' => 'We offer a wide range of services including haircuts, styling, coloring, manicures, pedicures, facials, massages, and more. Please check our app or website for a complete list of services.',
                ],
            ],
            // Add more FAQs as needed
        ];

        foreach ($faqs as $faq) {
            \App\Models\FAQ::create([
                'question' => $faq['question'],
                'answer' => $faq['answer'],
            ]);
        }

    }
}
