<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileCannotBeAdded;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {

        $customers = [
            [
                'first_name' => 'محمد',
                'last_name' => 'أحمد',
                'email' => 'mohamed.ahmed@gmail.com',
                'phone_number' => '966123456389',
                'date_of_birth' => '1992-05-20',
                'profile_picture' => asset('images/customer/1.png') ?? '',
            ],
            [
                'first_name' => 'فاطمة',
                'last_name' => 'خالد',
                'email' => 'fatima.khaled@gmail.com',
                'phone_number' => '966983654321',
                'date_of_birth' => '1985-11-12',
                'profile_picture' => asset('images/customer/2.jpg') ?? '',
            ],
            [
                'first_name' => 'عبد الله',
                'last_name' => 'محمود',
                'email' => 'abdullah.mahmoud@gmail.com',
                'phone_number' => '966535555455',
                'date_of_birth' => '1980-08-30',
                'profile_picture' => asset('images/customer/3.jpeg') ?? '',
            ],
            [
                'first_name' => 'نوره',
                'last_name' => 'سعيد',
                'email' => 'norah.saad@gmail.com',
                'phone_number' => '966113111111',
                'date_of_birth' => '1994-02-15',
                'profile_picture' => asset('images/customer/4.jpeg') ?? '',
            ],
            [
                'first_name' => 'أحمد',
                'last_name' => 'علي',
                'email' => 'ahmed.ali@gmail.com',
                'phone_number' => '966222722222',
                'date_of_birth' => '1978-07-25',
                'profile_picture' => asset('images/customer/5.jpeg') ?? '',
            ],
            [
                'first_name' => 'لمياء',
                'last_name' => 'راشد',
                'email' => 'lamia.rashid@gmail.com',
                'phone_number' => '966337333333',
                'date_of_birth' => '1989-12-10',
                'profile_picture' => asset('images/customer/7.jpeg') ?? '',
            ],
            [
                'first_name' => 'يوسف',
                'last_name' => 'خالد',
                'email' => 'youssef.khalid@gmail.com',
                'phone_number' => '966447444444',
                'date_of_birth' => '1997-03-05',
                'profile_picture' => asset('images/customer/10.jpeg') ?? '',
            ],
            [
                'first_name' => 'نورا',
                'last_name' => 'محمد',
                'email' => 'noura.mohamed@gmail.com',
                'phone_number' => '966575555355',
                'date_of_birth' => '1983-09-20',
                'profile_picture' => asset('images/customer/11.jpeg') ?? '',
            ],
            [
                'first_name' => 'خالد',
                'last_name' => 'عبدالله',
                'email' => 'khaled.abdullah@gmail.com',
                'phone_number' => '966667666636',
                'date_of_birth' => '1975-06-17',
                'profile_picture' => asset('images/customer/14.jpeg') ?? '',
            ],
            [
                'first_name' => 'ريم',
                'last_name' => 'محمود',
                'email' => 'reem.mahmoud@gmail.com',
                'phone_number' => '966777477777',
                'date_of_birth' => '1990-01-03',
                'profile_picture' => asset('images/customer/10.jpeg') ?? '',
            ],
            [
                'first_name' => 'سارة',
                'last_name' => 'محمد',
                'email' => 'sarah.mohamed@gmail.com',
                'phone_number' => '966886888888',
                'date_of_birth' => '1987-04-28',
                'profile_picture' => asset('images/customer/1.png') ?? '',
            ],
            [
                'first_name' => 'عبدالرحمن',
                'last_name' => 'علي',
                'email' => 'abdulrahman.ali@gmail.com',
                'phone_number' => '966799999999',
                'date_of_birth' => '1981-11-09',
                'profile_picture' => asset('images/customer/2.jpg') ?? '',
            ],
            [
                'first_name' => 'مريم',
                'last_name' => 'خالد',
                'email' => 'maryam.khalid@gmail.com',
                'phone_number' => '966121217121',
                'date_of_birth' => '1986-08-14',
                'profile_picture' => asset('images/customer/3.jpeg') ?? '',
            ],
            [
                'first_name' => 'أحمد',
                'last_name' => 'محمد',
                'email' => 'ahmed.mohamed@gmail.com',
                'phone_number' => '966233223232',
                'date_of_birth' => '1976-05-23',
                'profile_picture' => asset('images/customer/4.jpeg') ?? '',
            ],
            [
                'first_name' => 'ليلى',
                'last_name' => 'خالد',
                'email' => 'layla.khalid@gmail.com',
                'phone_number' => '966349934343',
                'date_of_birth' => '1995-10-11',
                'profile_picture' => asset('images/customer/5.jpeg') ?? '',
            ],
            [
                'first_name' => 'محمد',
                'last_name' => 'عبدالرحمن',
                'email' => 'mohamed.abdulrahman@gmail.com',
                'phone_number' => '966459947454',
                'date_of_birth' => '1984-03-26',
                'profile_picture' => asset('images/customer/7.jpeg') ?? '',
            ],
            [
                'first_name' => 'سلمى',
                'last_name' => 'محمود',
                'email' => 'salma.mahmoud@gmail.com',
                'phone_number' => '966555656565',
                'date_of_birth' => '1993-06-19',
                'profile_picture' => asset('images/customer/10.jpeg') ?? '',
            ],
            [
                'first_name' => 'علي',
                'last_name' => 'خالد',
                'email' => 'ali.khalid@gmail.com',
                'phone_number' => '966666767676',
                'date_of_birth' => '1982-12-07',
                'profile_picture' => asset('images/customer/11.jpeg') ?? '',
            ],
            [
                'first_name' => 'نور',
                'last_name' => 'أحمد',
                'email' => 'nour.ahmed@gmail.com',
                'phone_number' => '966784478787',
                'date_of_birth' => '1991-07-02',
                'profile_picture' => asset('images/customer/14.jpeg') ?? '',
            ],
            [
                'first_name' => 'مرام',
                'last_name' => 'محمد',
                'email' => 'maram.mohamed@gmail.com',
                'phone_number' => '966891189898',
                'date_of_birth' => '1988-04-16',
                'profile_picture' => asset('images/customer/10.jpeg') ?? '',
            ],
        ];

        foreach ($customers as $customer) {
            $newCustomer = Customer::factory()->create([
                'first_name' => $customer['first_name'],
                'last_name' => $customer['last_name'],
                'email' => $customer['email'],
                'phone_number' => $customer['phone_number'],
                'date_of_birth' => $customer['date_of_birth'],
            ]);
            if ($customer['profile_picture']) {
                try {
                    $newCustomer->addMediaFromUrl($customer['profile_picture'])->preservingOriginal()->toMediaCollection('profile_picture');
                } catch (FileDoesNotExist|FileCannotBeAdded $e) {
                }
            }
        }

    }
}
