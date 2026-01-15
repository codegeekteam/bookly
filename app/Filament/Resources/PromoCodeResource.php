<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PromoCodeResource\Pages;
use App\Models\PromoCode;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PromoCodeResource extends Resource
{
    protected static ?string $model = PromoCode::class;

    public static function getNavigationGroup(): string
    {
        return __('Sales');
    }

    public static function getLabel(): ?string
    {
        return __('Promo Code');
    }

    public static function getPluralLabel(): ?string
    {
        return __('Promo Codes');
    }

    public static function getNavigationLabel(): string
    {
        return __('Promo Codes');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('Main Information'))->schema([
                    TextInput::make('code')
                        ->label(__('Code'))
                        ->required(),

                    TextInput::make('discount_amount')
                        ->numeric()
                        ->minValue(0)
                        ->label(__('Discount Amount'))
                        ->required(),

                    TextInput::make('maximum_redeems')
                        ->numeric()
                        ->minValue(0)
                        ->label(__('Maximum Redeems'))
                        ->required(),

                    TextInput::make('maximum_discount')
                        ->numeric()
                        ->minValue(0)
                        ->label(__('Maximum Discount'))
                        ->required(),

                    Select::make('discount_type_id')
                        ->required()
                        ->label(__('Discount Type'))
                        ->relationship('discountType', 'title'),

                ])->columns(2),

                Section::make(__('Duration'))->schema([
                    DateTimePicker::make('start_date')
                        ->label(__('Start Date'))
                        ->required()
                        ->after('yesterday')
                        ->date(),
                    DateTimePicker::make('end_date')
                        ->label(__('End Date'))
                        ->required()
                        ->after('start_date')
                        ->date(),
                ])->columns(2),

                Section::make(__('Available For'))->schema([
                    Toggle::make('is_for_services')
                        ->live()
                        ->label(__('Services')),
                    Select::make('services')
                        ->label(__('Services'))
                        ->relationship('services', 'title')
                        ->searchable()
                        ->visible(fn (Get $get) => $get('is_for_services'))
                        ->columnSpanFull()
                        ->getOptionLabelFromRecordUsing(fn ($record) => $record->title)
                        ->preload()
                        ->multiple(),
                    Toggle::make('is_for_plans')
                        ->live()
                        ->label(__('Plans')),
                    Select::make('plans')
                        ->relationship('plans', 'title')
                        ->searchable()
                        ->label(__('Plans'))
                        ->visible(fn (Get $get) => $get('is_for_plans'))
                        ->columnSpanFull()
                        ->getOptionLabelFromRecordUsing(fn ($record) => $record->title)
                        ->preload()
                        ->multiple(),
                ])->columns(2),

                Section::make([
                    Placeholder::make('created_at')
                        ->label(__('Created Date'))
                        ->content(fn (?PromoCode $record): string => $record?->created_at?->diffForHumans() ?? '-'),

                    Placeholder::make('updated_at')
                        ->label(__('Last Modified Date'))
                        ->content(fn (?PromoCode $record): string => $record?->updated_at?->diffForHumans() ?? '-'),
                ])->columns(2),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->searchable()
                    ->label(__('Code'))
                    ->sortable(),
                TextColumn::make('DiscountType.title')
                    ->searchable()
                    ->label(__('Discount Type'))
                    ->sortable(),
                TextColumn::make('discount_amount')
                    ->searchable()
                    ->label(__('Discount Amount'))
                    ->sortable(),
                TextColumn::make('maximum_discount')
                    ->label(__('Maximum Discount'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('start_date')
                    ->label(__('Start Date'))
                    ->date()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('end_date')
                    ->label(__('End Date'))
                    ->date()
                    ->searchable()
                    ->sortable(),
                IconColumn::make('is_for_plans')
                    ->boolean()
                    ->label(__('For Plans')),
                IconColumn::make('is_for_services')
                    ->boolean()
                    ->label(__('For Services')),

            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPromoCodes::route('/'),
            'create' => Pages\CreatePromoCode::route('/create'),
            'edit' => Pages\EditPromoCode::route('/{record}/edit'),
        ];
    }
}
