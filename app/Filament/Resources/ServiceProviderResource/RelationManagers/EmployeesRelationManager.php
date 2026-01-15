<?php

namespace App\Filament\Resources\ServiceProviderResource\RelationManagers;

use App\Enums\ProviderTypeEnum;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class EmployeesRelationManager extends RelationManager
{
    protected static string $relationship = 'employees';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->label(__('Name'))
                    ->maxLength(255),
                TextInput::make('email')
                    ->label(__('Email'))
                    ->email()
                    ->unique(ignoreRecord: true)
                    ->required(),
                TextInput::make('phone_number')
                    ->label(__('Phone Number'))
                    ->required(),
                Select::make('services')
                    ->label(__('Services'))
                    ->multiple()
                    ->relationship('services', 'title')
                    ->options(function () {
                        // Only show services that belong to this service provider
                        return $this->ownerRecord->services()->pluck('title', 'services.id');
                    })
                    ->preload()
                    ->searchable(),
                SpatieMediaLibraryFileUpload::make('profile_picture')
                    ->label(__('Profile Picture'))
                    ->collection('profile_pictures')
                    ->image()
                    ->avatar(),
                Toggle::make('is_blocked')
                    ->label(__('Blocked')),
                Toggle::make('is_active')
                    ->label(__('Active')),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                SpatieMediaLibraryImageColumn::make('profile_picture')
                    ->collection('profile_pictures')
                    ->circular()
                    ->label(__('Image')),

                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label(__('Name')),

                TextColumn::make('email')
                    ->searchable()
                    ->sortable()
                    ->label(__('Email')),

                TextColumn::make('phone_number')
                    ->label(__('Phone Number')),

                TextColumn::make('services.title')
                    ->label(__('Services'))
                    ->badge()
                    ->separator(','),

                ToggleColumn::make('is_blocked')
                    ->label(__('Blocked')),

                ToggleColumn::make('is_active')
                    ->label(__('Active')),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function ($data) {
                        return array_merge($data, [
                            'provider_id' => $this->ownerRecord->id,
                        ]);
                    })
                    ->after(function ($record, $data) {
                        // Handle service attachment after employee creation
                        if (isset($data['services']) && is_array($data['services'])) {
                            $record->services()->sync($data['services']);
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->mutateFormDataUsing(function (array $data, $record): array {
                        // Load current services for the employee
                        $data['services'] = $record->services()->pluck('services.id')->toArray();
                        return $data;
                    })
                    ->after(function ($record, $data) {
                        // Handle service updates after employee update
                        if (isset($data['services'])) {
                            $record->services()->sync($data['services'] ?? []);
                        }
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

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return $ownerRecord->provider_type_id === ProviderTypeEnum::ENTERPRISE;
    }
}
