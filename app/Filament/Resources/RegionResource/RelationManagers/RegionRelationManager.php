<?php

namespace App\Filament\Resources\RegionResource\RelationManagers;

use App\Models\Region;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class RegionRelationManager extends RelationManager
{
    protected static string $relationship = 'regions';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('Regions');
    }

    public static function getLabel(): ?string
    {
        return __('Region');
    }

    public static function getPluralLabel(): ?string
    {
        return __('Regions');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([

                Section::make(__('Region Details'))->schema([
                    TextInput::make('arabic_title')->label(__('Arabic Title'))->required(),
                    TextInput::make('english_title')->label(__('English Title'))->required(),
                ])->columns(2),

            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
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
