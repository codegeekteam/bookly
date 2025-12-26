<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GiftCardThemeResource\Pages;
use App\Models\GiftCardTheme;
use Filament\Forms;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;

class GiftCardThemeResource extends Resource
{
    protected static ?string $model = GiftCardTheme::class;

    public static function getNavigationGroup(): string
    {
        return __('Gift Cards');
    }

    public static function getLabel(): ?string
    {
        return __('Gift Card Theme');
    }

    public static function getPluralLabel(): ?string
    {
        return __('Gift Card Themes');
    }

    public static function getNavigationLabel(): string
    {
        return __('Gift Card Themes');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('title.ar')
                    ->label(__('Arabic title'))
                    ->regex('/^[\x{0600}-\x{06FF}\s]+$/u')
                    ->required(),
                TextInput::make('title.en')
                    ->regex('/^[a-zA-Z0-9\s.,!?;:\'"(){}\[\]\-]+$/')
                    ->label(__('English title'))
                    ->required(),
                Toggle::make('active')->label(__('Active'))->default(true),

                SpatieMediaLibraryFileUpload::make('image')
                    ->required()
                    ->collection('main_image')
                    ->acceptedFileTypes(['image/jpg', 'image/jpeg', 'image/png'])
                    ->label(__('Main Image')),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->sortable()
                    ->label('ID'),

                TextColumn::make('title')
                    ->label(__('Title'))
                    ->searchable(query: function ($query, $search) {
                        $query->whereRaw('LOWER(title) LIKE ?', ['%' . strtolower($search) . '%']);
                    }),

                SpatieMediaLibraryImageColumn::make('main image')
                    ->label(__('Main Image'))
                    ->collection('main_image'),

                ToggleColumn::make('active')
                    ->label(__('Active'))
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                SelectFilter::make('active')
                    ->options([
                        1 => __('Active'),
                        0 => __('Not Active'),
                    ])
                    ->label(__('Active')),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                //Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                //Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGiftCardThemes::route('/'),
            'create' => Pages\CreateGiftCardTheme::route('/create'),
            'edit' => Pages\EditGiftCardTheme::route('/{record}/edit'),
        ];
    }
}
