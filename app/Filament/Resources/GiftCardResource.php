<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GiftCardResource\Pages;
use App\Filament\Resources\GiftCardResource\RelationManagers;
use App\Models\GiftCard;

use Carbon\Carbon;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Tabs;

class GiftCardResource extends Resource
{
    protected static ?string $model = GiftCard::class;

    public static function getNavigationGroup(): string
    {
        return __('Gift Cards');
    }

    public static function getLabel(): ?string
    {
        return __('Gift Card');
    }

    public static function getPluralLabel(): ?string
    {
        return __('Gift Cards');
    }

    public static function getNavigationLabel(): string
    {
        return __('Gift Cards');
    }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('code')
                    ->searchable()
                    ->label(__('Code'))
                    ->sortable(),
                TextColumn::make('amount')
                    ->searchable()
                    ->label(__('Amount'))
                    ->sortable(),
                TextColumn::make('payment_status')
                    ->searchable()
                    ->sortable()
                    ->label(__('Payment Status'))
                    ->formatStateUsing(function ($state) {
                        return match ($state) {
                            'paid' => __('Paid'),
                            'unpaid' => __('Unpaid'),
                            'partially_paid' => __('Partially Paid'),
                            default => __('Unknown'),
                            };
                     }),
                TextColumn::make('recipient_name')
                    ->label(__('Recipient Name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('recipient_phone_number')
                    ->label(__('Recipient Phone'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('Date'))
                    ->date()
                    ->searchable()
                    ->sortable(),
                IconColumn::make('is_used')
                    ->sortable()
                    ->boolean()
                    ->label(__('Is Used')),
            ])
            ->filters([
                //
                SelectFilter::make('payment_status')
                    ->options([
                        'paid' => __('Paid'),
                        'unpaid' => __('Unpaid'),
                    ])
                    ->label(__('Payment Status'))
                    ->attribute('payment_status'),

                SelectFilter::make('Is Used')
                    ->options([
                        1 =>  __('Used'),
                        0 =>  __('Not Used'),
                    ])
                    ->label(__('Is Used'))
                    ->attribute('is_used')
            ])
            ->actions([
                //Tables\Actions\EditAction::make(),
                Tables\Actions\EditAction::make()
                    ->label(__('Edit Payment Status'))
                    ->form([
                        \Filament\Forms\Components\Select::make('payment_status')
                            ->label(__('Payment Status'))
                            ->options([
                                'paid' => __('Paid'),
                                'unpaid' => __('Unpaid'),
                            ])
                            ->required(),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update(['payment_status' => $data['payment_status']]);
                    }),
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListGiftCards::route('/'),
            'view' => Pages\ViewGiftCard::route('/{record}'),
            //'create' => Pages\CreateGiftCard::route('/create'),
            //'edit' => Pages\EditGiftCard::route('/{record}/edit'),
        ];
    }


    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                // Create tabs component
                Tabs::make('gift_card_tabs')
                    ->columnSpan(12)
                    ->tabs([
                        // Tab 1: Basic Information
                        Tabs\Tab::make(__('Basic Information'))
                            ->icon('heroicon-o-document-text')
                            ->schema([
                                TextEntry::make('code')
                                    ->label(__('Code')),
                                TextEntry::make('amount')
                                    ->label(__('Amount')),
                                TextEntry::make('created_at')
                                    ->label(__('Date'))
                                    ->formatStateUsing(function ($state) {
                                        return Carbon::parse($state)->format('d M Y, h:i A'); // Customize your format here
                                    }),
                                Infolists\Components\IconEntry::make('is_used')
                                    ->boolean()
                                    ->label(__('Is Used')),
                                TextEntry::make('used_at')
                                    ->visible(fn($record) => $record->is_used == 1)
                                    ->label(__('Used At'))
                                    ->formatStateUsing(function ($state) {
                                        return $state ? Carbon::parse($state)->format('d M Y, h:i A') : 'Not used';
                                    }),
                                TextEntry::make('payment_status')
                                    ->label(__('Payment Status'))
                            ]),

                        // Tab 2: User Information
                        Tabs\Tab::make(__('User Information'))
                            ->icon('heroicon-o-identification')
                            ->schema([
                                TextEntry::make('user.id')
                                    ->label(__('ID'))
                                    ->default(fn($record) => $record->user->id ?? 'N/A'),
                                TextEntry::make('user.name')
                                    ->label(__('Name'))
                                    ->default(fn($record) => $record->user->name ?? 'N/A'),
                                TextEntry::make('user.email')
                                    ->label(__('Email'))
                                    ->default(fn($record) => $record->user->email ?? 'N/A'),
                            ]),

                        // Tab 3: Recipient Information
                        Tabs\Tab::make(__('Recipient Information'))
                            ->icon('heroicon-o-user-circle')
                            ->schema([
                                TextEntry::make('recipient_name')
                                    ->label(__('Recipient Name')),
                                TextEntry::make('recipient_phone_number')
                                    ->label(__('Recipient Phone')),
                            ]),


                        // Tab 4: used user Information
                        Tabs\Tab::make(__('Used By Information'))
                            ->icon('heroicon-o-user-group')
                            ->visible(fn($record) => $record->usedBy !== null) // Show tab only if user is not null
                            ->schema([
                                TextEntry::make('usedBy.id')
                                    ->label(__('ID '))
                                    ->default(fn($record) => $record->usedBy->id ?? 'N/A'),

                                TextEntry::make('usedBy.first_name')
                                    ->label(__('First Name'))
                                    ->default(fn($record) => $record->usedBy->first_name ?? 'N/A'),

                                TextEntry::make('usedBy.phone_number')
                                    ->label(__('Phone'))
                                    ->default(fn($record) => $record->usedBy->phone_number ?? 'N/A'),
                            ])
                    ]),
            ]);
    }
}
