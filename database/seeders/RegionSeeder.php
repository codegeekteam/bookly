<?php

namespace Database\Seeders;

use App\Models\Region;
use Illuminate\Database\Seeder;

class RegionSeeder extends Seeder
{
    public function run(): void
    {
        $KsaRegions = [
            ['en' => 'Riyadh Region', 'ar' => 'منطقة الرياض'],
            ['en' => 'Makkah Region', 'ar' => 'منطقة مكة المكرمة'],
            ['en' => 'Al-Madinah Region', 'ar' => 'منطقة المدينة المنورة'],
            ['en' => 'Eastern Province', 'ar' => 'المنطقة الشرقية'],
            ['en' => 'Al-Qassim Region', 'ar' => 'منطقة القصيم'],
            ['en' => 'Hail Region', 'ar' => 'منطقة حائل'],
            ['en' => 'Tabuk Region', 'ar' => 'منطقة تبوك'],
            ['en' => 'Najran Region', 'ar' => 'منطقة نجران'],
            ['en' => 'Jizan Region', 'ar' => 'منطقة جازان'],
            ['en' => 'Asir Region', 'ar' => 'منطقة عسير'],
            ['en' => 'Al-Baha Region', 'ar' => 'منطقة الباحة'],
            ['en' => 'Northern Border Region', 'ar' => 'منطقة الحدود الشمالية'],
            ['en' => 'Al-Jawf Region', 'ar' => 'منطقة الجوف']];

        foreach ($KsaRegions as $region) {
            Region::create(['title' => $region]);
        }
    }
}
