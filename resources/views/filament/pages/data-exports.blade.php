<x-filament-panels::page>
    <x-filament::section heading="{{ __('Download Excel Reports') }}">
        <p class="text-sm text-gray-600 dark:text-gray-400">
            {{ __('Use the buttons above to export daily wash logs, expenses, or monthly site P&L as Excel files.') }}
        </p>
        <ul class="mt-4 list-disc space-y-1 pl-5 text-sm text-gray-600 dark:text-gray-400">
            <li>{{ __('Daily Logs — wash entries by date range') }}</li>
            <li>{{ __('Expenses — all expense records by date range') }}</li>
            <li>{{ __('Site P&L — monthly profit & loss for all sites') }}</li>
        </ul>
    </x-filament::section>
</x-filament-panels::page>
