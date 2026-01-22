<?php

namespace Database\Seeders;

use App\Models\Address;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ModelMorphMapRemoveSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $addresses_provider = Address::where('addressable_type', 'provider')->get();
        $addresses_customer = Address::where('addressable_type', 'customer')->get();
        foreach($addresses_provider as $address_p) {
            $address_p->update(['addressable_type' => 'App\Models\ServiceProvider']);
        }
        foreach($addresses_customer as $address_c) {
            $address_c->update(['addressable_type' => 'App\Models\Customer"']);
        }
    }
}
