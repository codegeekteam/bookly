<x-filament::page>
    {{ $this->form }}
    <div class="mt-4 w-fit">
        <x-filament::button wire:click="save" size="sm">
            Save Settings
        </x-filament::button>
    </div>
</x-filament::page>

