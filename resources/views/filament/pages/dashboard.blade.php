<x-filament-panels::page>
    <style>
        @media print {
            .fi-sidebar, .fi-topbar, .fi-header-actions, .fi-page-header-main-ctn, form {
                display: none !important;
            }
            .fi-main { padding: 0 !important; margin: 0 !important; }
            .fi-wi-stats-overview-stat { break-inside: avoid; }
            .fi-section { break-inside: avoid; box-shadow: none !important; border: 1px solid #ccc !important; }
            body { background: white !important; }
        }
    </style>

    <!-- Filament renders the form automatically now -->
    {{ $this->filtersForm }}

    <!-- Filament's widget renderer -->
    <x-filament-widgets::widgets
        :columns="$this->getColumns()"
        :data="$this->getWidgetsData()"
    />
</x-filament-panels::page>