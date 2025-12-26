<?php

namespace App\Filament\Resources\PayoutResource\Pages;

use App\Filament\Resources\PayoutResource;
use App\Services\PayoutService;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewPayout extends ViewRecord
{
    protected static string $resource = PayoutResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('markAsTransferred')
                ->label('Mark as Transferred')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->form([
                    Forms\Components\FileUpload::make('receipt')
                        ->label('Transfer Receipt (Optional)')
                        ->directory('payout-receipts')
                        ->disk('public')
                        ->image()
                        ->maxSize(5120),
                ])
                ->action(function (array $data) {
                    $payoutService = app(PayoutService::class);
                    $receipt = $data['receipt'] ?? null;

                    $payoutService->markAsTransferred($this->record, $receipt);

                    Notification::make()
                        ->title('Payout Transferred')
                        ->success()
                        ->body('The payout has been marked as transferred and email sent to provider.')
                        ->send();

                    $this->redirect(static::getResource()::getUrl('index'));
                })
                ->requiresConfirmation()
                ->visible(fn () => $this->record->status === 'pending'),

            Actions\Action::make('cancel')
                ->label('Cancel')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->form([
                    Forms\Components\Textarea::make('note')
                        ->label('Cancellation Reason')
                        ->required()
                        ->rows(3),
                ])
                ->action(function (array $data) {
                    $payoutService = app(PayoutService::class);
                    $payoutService->cancelPayout($this->record, $data['note']);

                    Notification::make()
                        ->title('Payout Cancelled')
                        ->warning()
                        ->body('The payout has been cancelled and amounts returned to pending.')
                        ->send();

                    $this->redirect(static::getResource()::getUrl('index'));
                })
                ->requiresConfirmation()
                ->visible(fn () => $this->record->status === 'pending'),
        ];
    }

    public function getFooterWidgets(): array
    {
        return [
            PayoutResource\Widgets\DeferredPayoutsTable::class,
        ];
    }
}
