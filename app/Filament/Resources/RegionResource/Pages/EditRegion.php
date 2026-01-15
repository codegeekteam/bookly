<?php

namespace App\Filament\Resources\RegionResource\Pages;

use App\Filament\Resources\RegionResource;
use Filament\Pages\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRegion extends EditRecord
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

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['title'] = [
            'ar' => $data['arabic_title'],
            'en' => $data['english_title'],
        ];

        return $data;
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['arabic_title'] = $data['title']['ar'];
        $data['english_title'] = $data['title']['en'];

        return $data;
    }

    protected function getActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
