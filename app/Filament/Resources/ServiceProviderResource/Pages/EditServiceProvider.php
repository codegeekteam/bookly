<?php

namespace App\Filament\Resources\ServiceProviderResource\Pages;

use App\Filament\Resources\ServiceProviderResource;
use Filament\Pages\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditServiceProvider extends EditRecord
{
    protected static string $resource = ServiceProviderResource::class;

    protected function getActions(): array
    {
        return [
            //DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['social'] = [
            'twitter' => $data['twitter'],
            'snapchat' => $data['snapchat'],
            'instagram' => $data['instagram'],
            'tiktok' => $data['tiktok'],
        ];

        return $data;
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['twitter'] = $data['social']['twitter'] ?? null;
        $data['snapchat'] = $data['social']['snapchat'] ?? null;
        $data['instagram'] = $data['social']['instagram'] ?? null;
        $data['tiktok'] = $data['social']['tiktok'] ?? null;

        return $data;
    }

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
