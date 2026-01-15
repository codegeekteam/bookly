<x-filament::page>
    <div class="space-y-6">
        <!-- Referral Code and Referrer Details -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Referral Code -->
                <div class="flex flex-col">
                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('Referral Code') }}</span>
                    <span class="text-lg font-semibold text-gray-800 dark:text-gray-200">{{ $referCode }}</span>
                </div>

                <!-- Referrer -->
                <div class="flex flex-col">
                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('Referrer') }}</span>
                    <span class="text-lg font-semibold text-gray-800 dark:text-gray-200">
                        {{ $referrer ? $referrer->first_name . ' ' . $referrer->last_name : __('--') }}
                    </span>
                </div>

                <!-- Referral Count -->
                <div class="flex flex-col">
                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('Referral Count') }}</span>
                    <span class="text-lg font-semibold text-gray-800 dark:text-gray-200">{{ $referralCount }}</span>
                </div>
            </div>
        </div>

        <!-- Referrals Table -->
        <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
            @livewire(App\Livewire\Referrals::class, ['record' => $record])
        </div>
    </div>
</x-filament::page>
