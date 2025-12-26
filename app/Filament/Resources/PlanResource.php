<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PlanResource\Pages;
use App\Filament\Resources\PlanResource\RelationManagers\ItemsRelationManager;
use App\Models\Plan;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PlanResource extends Resource
{
    protected static ?string $model = Plan::class;


    public static function getNavigationGroup(): string
    {
        return __('Subscriptions');
    }

    public static function getLabel(): ?string
    {
        return __('Plan');
    }

    public static function getPluralLabel(): ?string
    {
        return __('Plans');
    }

    public static function getNavigationLabel(): string
    {
        return __('Plans');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('Plan Details'))
                    ->columns(2)
                    ->schema([
                        TextInput::make('number_of_months')
                            ->label(__('number of months'))
                            ->integer()
                            ->minValue(1)
                            ->maxValue(24)
                            ->required(),


                        TextInput::make('price')
                            ->label(__('Price'))
                            ->numeric()
                            ->minValue(0)
                            ->suffix(__('SAR'))
                            ->validationAttribute('Price')
                            ->required(),
                        Forms\Components\Toggle::make('active')
                            ->translateLabel()
                            ->required()
                            ->default(true),
                        FileUpload::make('image')
                            ->directory('plans')
                            ->image()
                            ->imageEditor()
                            ->nullable()
                            ->translateLabel(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('price')
                    ->translateLabel()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('number_of_months')
                    ->label(__('number of months'))
                    ->translateLabel()
                    ->searchable()
                    ->sortable(),
                Tables\Columns\ImageColumn::make('image')
                    ->translateLabel(),
                Tables\Columns\ToggleColumn::make('active')->sortable()->translateLabel(),
            ])
            ->filters([
                //
                SelectFilter::make('active')
                    ->options([
                        1 => __('Active'),
                        0 => __('Not Active'),
                    ])
                    ->label(__('Active')),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    //Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            ItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPlans::route('/'),
            'create' => Pages\CreatePlan::route('/create'),
            'edit' => Pages\EditPlan::route('/{record}/edit'),
        ];
    }
}
