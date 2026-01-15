<?php

namespace App\Filament\Resources\ServiceProviderResource\Pages;

use App\Filament\Resources\ServiceProviderResource;
use Filament\Resources\Pages\CreateRecord;

class CreateServiceProvider extends CreateRecord
{
    protected static string $resource = ServiceProviderResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['social'] = [
            'twitter' => $data['twitter'],
            'snapchat' => $data['snapchat'],
            'instagram' => $data['instagram'],
            'tiktok' => $data['tiktok'],
        ];

        return $data;
    }
}
