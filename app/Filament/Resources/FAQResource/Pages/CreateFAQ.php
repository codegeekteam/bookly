<?php

namespace App\Filament\Resources\FAQResource\Pages;

use App\Filament\Resources\FAQResource;
use Filament\Resources\Pages\CreateRecord;

class CreateFAQ extends CreateRecord
{
    protected static string $resource = FAQResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['question'] = [
            'ar' => $data['arabic_question'],
            'en' => $data['english_question'],
        ];

        $data['answer'] = [
            'ar' => $data['arabic_answer'],
            'en' => $data['english_answer'],
        ];

        return $data;
    }

    protected function getActions(): array
    {
        return [

        ];
    }
}
