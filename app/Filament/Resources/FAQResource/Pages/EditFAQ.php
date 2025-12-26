<?php

namespace App\Filament\Resources\FAQResource\Pages;

use App\Filament\Resources\FAQResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditFAQ extends EditRecord
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

    protected function mutateFormDataBeforeSave(array $data): array
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

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['arabic_question'] = $data['question']['ar'];
        $data['english_question'] = $data['question']['en'];

        $data['arabic_answer'] = $data['answer']['ar'];
        $data['english_answer'] = $data['answer']['en'];

        return $data;
    }

    protected function getActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
