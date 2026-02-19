<?php

namespace App\Filament\Pages;

use App\Models\RefundSetting;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Notifications\Notification;

class RefundSettings extends Page
{
    protected static ?string $navigationGroup = 'Refund';
    protected static ?string $navigationLabel = 'Refund Settings';
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static string $view = 'filament.pages.refund-settings';

    public ?array $data = [];

    public function mount(): void
    {
        // create one row if table is empty
        $settings = RefundSetting::firstOrCreate([]);

        // fill the form with current settings
        $this->form->fill($settings->toArray());
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Toggle::make('bank_account_refund')
                    ->label('Enable Bank Account Refund')
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state) {
                            // If bank refund is enabled, disable wallet refund
                            $set('wallet_refund', false);
                        }
                    }),

                Toggle::make('wallet_refund')
                    ->label('Enable Wallet Refund')
                     ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state) {
                            // If wallet refund is enabled, disable bank refund
                            $set('bank_account_refund', false);
                        }
                    }),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        RefundSetting::first()->update($this->data);

        Notification::make()
            ->title('Refund settings updated.')
            ->success()
            ->send();
    }
}
