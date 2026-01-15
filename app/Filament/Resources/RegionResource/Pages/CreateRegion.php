<?php

namespace App\Filament\Resources\RegionResource\Pages;

use App\Filament\Resources\RegionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateRegion extends CreateRecord
{
    protected static string $resource = RegionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['title'] = [
            'ar' => $data['arabic_title'],
            'en' => $data['english_title'],
        ];

        return $data;
    }
}
