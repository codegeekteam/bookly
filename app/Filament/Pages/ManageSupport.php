<?php

namespace App\Filament\Pages;

use App\Settings\SupportSettings;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\SettingsPage;
use Illuminate\Contracts\Support\Htmlable;

class ManageSupport extends SettingsPage
{
    //protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string $settings = SupportSettings::class;

    public static function getNavigationLabel(): string
    {
        return __('Manage Support');
    }

    public function getTitle(): string|Htmlable
    {
        return __('Manage Support');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Main Settings');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('Support Information'))
                    ->icon('heroicon-o-information-circle')
                    ->schema([

                        TextInput::make('email')
                            ->email()
                            ->required()
                            ->label(__('Email')),
                        TextInput::make('phone_number')
                            ->required()
                            ->tel()
                            ->label(__('Phone Number')),
                        TextInput::make('whatsapp_phone_number')
                            ->required()
                            ->tel()
                            ->label(__('WhatsApp Phone Number')),
                    ])->columns(2),
                Section::make(__('download app settings'))
                    ->icon('heroicon-o-information-circle')
                    ->schema([
                        TextInput::make('app_store_link')
                            ->required()
                            ->url()
                            ->label(__('App Store Link')),
                        TextInput::make('google_play_link')
                            ->required()
                            ->url()
                            ->label(__('google play link')),
                    ])->columns(2)
            ]);
    }
}
