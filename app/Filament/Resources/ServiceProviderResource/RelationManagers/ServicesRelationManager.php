<?php

namespace App\Filament\Resources\ServiceProviderResource\RelationManagers;

use App\Models\AttachedService;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

class ServicesRelationManager extends RelationManager
{
    protected static string $relationship = 'attachedServices';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('Provided Services');
    }

    protected static function getModelLabel(): ?string
    {
        return __('Provided Services');
    }

    public function getTablePluralModelLabel(): ?string
    {
        return __('Provided Service');
    }

    protected function getTableHeading(): ?string
    {
        return __('Provided Services');
    }

    protected static function getPluralModelLabel(): ?string
    {
        return __('Provided Service');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('Service Details'))->schema([

                    Select::make('service_id')
                        ->label(__('Service'))
                        ->relationship('service', 'title')
                        ->options(\App\Models\Service::get()->pluck('title', 'id')->toArray())
                        ->required()
                        ->preload(),

                    TextInput::make('price')
                        ->label(__('Price'))
                        ->required(),

                    Select::make('delivery_types')
                        ->label(__('Delivery Types'))
                        ->relationship('deliveryTypes', 'title')
                        ->options(\App\Models\DeliveryType::get()->pluck('title', 'id')->toArray())
                        ->multiple()
                        ->required()
                        ->preload(),

                ])->columns(2),

                Section::make()->schema([
                    Placeholder::make('created_at')
                        ->label(__('Created Date'))
                        ->content(fn (?AttachedService $record): string => $record?->created_at?->diffForHumans() ?? '-'),

                    Placeholder::make('updated_at')
                        ->label(__('Last Modified Date'))
                        ->content(fn (?AttachedService $record): string => $record?->updated_at?->diffForHumans() ?? '-'),
                ])->columns(2),

            ]);

    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('service.title')
                    ->label(__('Service'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('service.description')
                    ->label(__('Description'))
                    ->wrap()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('price')
                    ->label(__('Price'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('deliveryTypes.title')
                    ->label(__('Delivery Types'))
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->separator(','),
                TextColumn::make('service.categories.title')
                    ->label(__('Categories'))
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->separator(','),
            ])
            ->filters([

            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    ExportBulkAction::make(),
                ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }

    public static function getLabel(): ?string
    {
        return __('Provided Services');
    }

    public function getHeading(): ?string
    {
        return __('Provided Services');
    }
}
