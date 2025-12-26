<x-filament::page>
    <div class="space-y-6">

        <!-- customers Table -->
        <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
            @livewire(App\Livewire\RedeemedPointsCustomers::class, ['record' => $record])
        </div>
    </div>
</x-filament::page>
