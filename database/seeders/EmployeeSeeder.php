<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {

        $employees = [
            [
                'name' => 'محمد أحمد',
                'email' => 'mohamed.ahmed1@gmail.com',
                'phone_number' => '966500000001',
            ],
            [
                'name' => 'فاطمة خالد',
                'email' => 'fatima.khaled2@gmail.com',
                'phone_number' => '966500000002',
            ],
            [
                'name' => 'عبد الله محمود',
                'email' => 'abdullah.mahmoud3@gmail.com',
                'phone_number' => '966500000003',
            ],
            [
                'name' => 'نورة علي',
                'email' => 'noura.ali4@gmail.com',
                'phone_number' => '966500000004',
            ],
            [
                'name' => 'ياسر عبد الرحمن',
                'email' => 'yasser.abdulrahman5@gmail.com',
                'phone_number' => '966500000005',
            ],
            [
                'name' => 'لمى محمد',
                'email' => 'lama.mohammed6@gmail.com',
                'phone_number' => '966500000006',
            ],
            [
                'name' => 'عبد العزيز سلطان',
                'email' => 'abdulaziz.sultan7@gmail.com',
                'phone_number' => '966500000007',
            ],
            [
                'name' => 'ريما عبد الله',
                'email' => 'reema.abdullah8@gmail.com',
                'phone_number' => '966500000008',
            ],
            [
                'name' => 'سعود علي',
                'email' => 'saud.ali9@gmail.com',
                'phone_number' => '966500000009',
            ],
            [
                'name' => 'مريم حسن',
                'email' => 'maryam.hasan10@gmail.com',
                'phone_number' => '966500000010',
            ],
            [
                'name' => 'علي محمود',
                'email' => 'ali.mahmoud11@gmail.com',
                'phone_number' => '966500000011',
            ],
            [
                'name' => 'نور محمد',
                'email' => 'nour.mohammed12@gmail.com',
                'phone_number' => '966500000012',
            ],
            [
                'name' => 'محمد سعيد',
                'email' => 'mohammed.saied13@gmail.com',
                'phone_number' => '966500000013',
            ],
            [
                'name' => 'ليلى عبد الله',
                'email' => 'laila.abdullah14@gmail.com',
                'phone_number' => '966500000014',
            ],
            [
                'name' => 'عبد الرحمن حسن',
                'email' => 'abdulrahman.hasan15@gmail.com',
                'phone_number' => '966500000015',
            ],
            [
                'name' => 'هدى علي',
                'email' => 'huda.ali16@gmail.com',
                'phone_number' => '966500000016',
            ],
            [
                'name' => 'سارة عبد الرحمن',
                'email' => 'sarah.abdulrahman17@gmail.com',
                'phone_number' => '966500000017',
            ],
            [
                'name' => 'محمود فاطمة',
                'email' => 'mahmoud.fatima18@gmail.com',
                'phone_number' => '966500000018',
            ],
            [
                'name' => 'رنا سعيد',
                'email' => 'rana.saied19@gmail.com',
                'phone_number' => '966500000019',
            ],
        ];

        foreach ($employees as $employee) {
            \App\Models\Employee::create([
                'name' => $employee['name'],
                'email' => $employee['email'],
                'phone_number' => $employee['phone_number'],
            ]);
        }

    }
}
