<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class GenerateReferCodeSeeder extends Seeder
{
    public function run()
    {
        // Fetch all users without a refer code
        $users = Customer::whereNull('refer_code')->get();

        foreach ($users as $user) {
            // Generate a unique refer code for each user
            $user->refer_code = $this->generateReferCode();
            $user->save();
        }
    }

    private function generateReferCode()
    {
        do {
            // Generate a random 6-character string
            $referCode = Str::random(6);
        } while (Customer::where('refer_code', $referCode)->exists());

        return $referCode;
    }
}
