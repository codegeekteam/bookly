<?php

namespace App\Filament\Resources\PlanResource\RelationManagers;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    public function getLabel(): ?string
    {
        return __('Item');
    }

    public static function getPluralLabel(): ?string
    {
        return __('Items');
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('Items');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('arabic_title')
                    ->label(__('Arabic Title'))
                    ->regex('/^[\x{0600}-\x{06FF}\s]+$/u')
                    ->required()
                    ->maxLength(255),

                TextInput::make('english_title')
                    ->label(__('English Title'))
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label(__('Title'))
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                ->mutateFormDataUsing(function (array $data): array {
                    $data['title'] = [
                        'ar' => $data['arabic_title'],
                        'en' => $data['english_title'],
                    ];

                    return $data;
                }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()

                    ->mutateRecordDataUsing(function ($data) {
                        $data['arabic_title'] = $data['title']['ar'];
                        $data['english_title'] = $data['title']['en'];
                        return $data;
                    })
                    ->mutateFormDataUsing(function ($data) {
                        $data['title'] = [
                            'ar' => $data['arabic_title'],
                            'en' => $data['english_title'],
                        ];
                        return $data;
                    }),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }
}
