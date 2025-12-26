<?php

namespace App\Filament\Pages;

use App\Settings\RewardsSettings;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\SettingsPage;
use Illuminate\Contracts\Support\Htmlable;

class ManageRewards extends SettingsPage
{
    //protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string $settings = RewardsSettings::class;

    public static function getNavigationLabel(): string
    {
        return __('Manage Rewards');
    }

    public function getTitle(): string|Htmlable
    {
        return __('Manage Rewards');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Main Settings');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('Manage Rewards'))
                    ->icon('heroicon-o-information-circle')
                    ->schema([
                        TextInput::make('referral_bonus')
                            ->label(__('Referral Bonus'))
                            ->numeric()
                            ->minValue(0)  // Set a minimum value
                            ->maxValue(10000) // Set a maximum value
                            ->required(),
                        TextInput::make('riyals_per_point')
                            ->label(__('Riyals Per Point '))
                            ->numeric()
                            ->minValue(0)  // Set a minimum value
                            ->maxValue(10000)
                            ->rule('integer') // Ensures an integer value and sets a minimum
                            ->required(),
                    ])->columns(2)
            ]);
    }
}
