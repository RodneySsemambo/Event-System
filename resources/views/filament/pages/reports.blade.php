<x-filament-panels::page>
    <form wire:submit="download">
        {{ $this->form }}

        <div class="mt-6 flex gap-4">
            <x-filament::button type="submit" icon="heroicon-o-arrow-down-tray">
                Download Report
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>