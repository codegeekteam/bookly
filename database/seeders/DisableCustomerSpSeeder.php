<?php

namespace Database\Seeders;

use App\Models\ServiceProvider;
use App\Models\Customer;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DisableCustomerSpSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $phoneCustomers =  [   
                                 '536132530',//playstore otp
                                // '949781880', 
                                '532335103' //test otp
                            ];
         $phoneSps =  [   
                            '054441775',//playstore otp
                            // '555211552',
                            // '984622039',
                            // '963963963',
                            // '953991821',
                            // '940094773',
                            // '580580580',
                            '544417758'   //test otp             
                        ];
        $customers = Customer::whereNotIn('phone_number', $phoneCustomers)->get();       
        foreach($customers as $customer) {
            $customer->update(['is_blocked' => 1]); //is_blocked 1 - disabled , 0 -enabled
        }

        $sps = ServiceProvider::whereNotIn('phone_number', $phoneSps)->get();
        foreach($sps as $sp) {
            $sp->update([
                            'is_blocked' => 1, //disabled
                           // 'is_active' => 0   //approval pending
                        ]);
        }
    }
}
