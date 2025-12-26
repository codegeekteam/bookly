<?php

namespace App\Filament\Resources\ServiceResource\Pages;

use App\Filament\Resources\ServiceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditService extends EditRecord
{
    protected static string $resource = ServiceResource::class;

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

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
