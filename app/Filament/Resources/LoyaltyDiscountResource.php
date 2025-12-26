<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LoyaltyDiscountResource\Pages;
use App\Filament\Resources\LoyaltyDiscountResource\RelationManagers;
use App\Models\LoyaltyDiscount;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LoyaltyDiscountResource extends Resource
{
    protected static ?string $model = LoyaltyDiscount::class;

    public static function getNavigationGroup(): string
    {
        return __('Loyalty Points');
    }

    public static function getLabel(): ?string
    {
        return __('Loyalty Discounts');
    }

    public static function getPluralLabel(): ?string
    {
        return __('Loyalty Discounts');
    }

    public static function getNavigationLabel(): string
    {
        return __('Loyalty Discounts');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('Main Information'))->schema([

                    Select::make('discount_type_id')
                        ->required()
                        ->label(__('Discount Type'))
                        ->relationship('discountType', 'title')
                        ->reactive(),

                    TextInput::make('discount_amount')
                        ->numeric()
                        ->minValue(1) // 1 for percentage
                        ->maxValue(fn ($get) => $get('discount_type_id') == 2 ? 100 : null)
                        ->label(__('Discount Amount'))
                        ->required(),

                    TextInput::make('points')
                        ->numeric()
                        ->rule('integer')
                        ->minValue(1)
                        ->label(__('Points'))
                        ->required(),

                    TextInput::make('maximum_discount')
                        ->numeric()
                        ->minValue(1)
                        ->label(__('Maximum Discount'))
                        ->required()
                        ->visible(fn ($get) => $get('discount_type_id') == '2'),

                    TextInput::make('minimum_amount')
                        ->numeric()
                        ->minValue(1)
                        ->label(__('Minimum Amount'))
                        ->required(),

                ])->columns(2),

                Section::make(__('Duration'))->schema([
                    DateTimePicker::make('start_date')
                        ->label(__('Start Date'))
                        ->required()
                        ->date(),
                    DateTimePicker::make('end_date')
                        ->label(__('End Date'))
                        ->required()
                        ->after('start_date')
                        ->date(),
                ])->columns(2),

                Section::make([
                    Placeholder::make('created_at')
                        ->label(__('Created Date'))
                        ->content(fn (?LoyaltyDiscount $record): string => $record?->created_at?->diffForHumans() ?? '-'),

                    Placeholder::make('updated_at')
                        ->label(__('Last Modified Date'))
                        ->content(fn (?LoyaltyDiscount $record): string => $record?->updated_at?->diffForHumans() ?? '-'),
                ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('points')
                    ->searchable()
                    ->label(__('Points'))
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
                TextColumn::make('minimum_amount')
                    ->label(__('Minimum Amount'))
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
                TextColumn::make('redemptions_count')
                    ->label(__('Redeemed Customers'))
                    ->sortable()
                    ->getStateUsing(fn (LoyaltyDiscount $record) => $record->loyaltyDiscountCustomers->count()),

            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Action::make('viewRedeemedCustomers')
                    ->label (__('Redeemed Customers'))
                    ->icon('heroicon-o-user-group')
                    ->url(fn ($record) => static::getUrl('redeemed-customers', ['record' => $record->id]))
                    ->requiresConfirmation(false),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    //Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListLoyaltyDiscounts::route('/'),
            'create' => Pages\CreateLoyaltyDiscount::route('/create'),
            'edit' => Pages\EditLoyaltyDiscount::route('/{record}/edit'),
            'redeemed-customers' => Pages\RedeemedCustomers::route('/{record}/redeemed-customers'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('loyaltyDiscountCustomers');
    }
}
