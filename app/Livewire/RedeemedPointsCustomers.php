<?php

namespace App\Livewire;

use App\Models\Customer;
use App\Models\LoyaltyDiscount;
use App\Models\LoyaltyDiscountCustomer;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Component;
use Illuminate\Contracts\View\View;

class RedeemedPointsCustomers extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public $record;
    public function mount(LoyaltyDiscount $record)
    {
        $this->record = $record;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(LoyaltyDiscountCustomer::where('loyalty_discount_id',$this->record->id)->with('customer'))
                ->columns([
                    TextColumn::make('customer.first_name') // Accessing 'first_name' of the related customer
                    ->label(__('First Name'))
                        ->searchable(),
                    TextColumn::make('customer.last_name') // Accessing 'last_name' of the related customer
                    ->label(__('Last Name'))
                        ->searchable(),
                    TextColumn::make('created_at')
                        ->label(__('Created Date'))
                        ->formatStateUsing(fn (?LoyaltyDiscountCustomer $record): string => $record?->created_at?->diffForHumans() ?? '-'),
            ])
            ->filters([
                //
            ])
            ->actions([
                //
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    //
                ]),
            ])->emptyStateHeading(__('No Customers Redeemed'));
    }

    public function render(): View
    {
        return view('livewire.redeemed-points-customers');
    }
}
