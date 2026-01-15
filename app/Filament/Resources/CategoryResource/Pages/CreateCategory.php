<?php

namespace App\Filament\Resources\CategoryResource\Pages;

use App\Filament\Resources\CategoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCategory extends CreateRecord
{
    protected static string $resource = CategoryResource::class;

    public function mutateFormDataBeforeCreate(array $data): array
    {
        $data['title'] = [
            'ar' => $data['arabic_title'],
            'en' => $data['english_title'],
        ];

        return $data;

    }

    protected function getRedirectUrl(): string
    {
        return url('/admin/categories');
    }
}
