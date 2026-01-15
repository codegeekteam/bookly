<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HomeSectionResource\Pages;
use App\Filament\Resources\HomeSectionResource\RelationManagers;
use App\Models\HomeSection;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class HomeSectionResource extends Resource
{
    protected static ?string $model = HomeSection::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getLabel(): ?string
    {
        return __('Home Sections');
    }

    public static function getPluralLabel(): ?string
    {
        return __('Home Sections');
    }

    public static function getNavigationLabel(): string
    {
        return __('Home Sections');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                /*Forms\Components\TextInput::make('order_column')
                    ->numeric()
                    ->required()
                    ->default(0),*/

                // BelongsToMany field for Service Providers
                Forms\Components\Select::make('providers')
                    ->label('Service Providers')
                    ->required()
                    ->multiple()
                    ->relationship('providers', 'name', function ($query) {
                        $query->where('is_active', 1)->where('is_blocked', 0)->whereNotNull('name'); // Fetch only active providers
                    })
                    ->preload()
                    ->columnSpanFull(),  // Preload options to avoid multiple DB queries
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->label('Title')->sortable()->searchable(),
                //Tables\Columns\TextColumn::make('order_column')->label('Order')->sortable(),
                Tables\Columns\TextColumn::make('created_at')->label('Created At')->dateTime(),
                //Tables\Columns\TextColumn::make('updated_at')->label('Updated At')->dateTime(),
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make(),
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
            'index' => Pages\ListHomeSections::route('/'),
            'create' => Pages\CreateHomeSection::route('/create'),
            'edit' => Pages\EditHomeSection::route('/{record}/edit'),
        ];
    }
}
