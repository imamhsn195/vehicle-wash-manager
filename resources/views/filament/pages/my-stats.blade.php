<x-filament-panels::page>
    <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
        @if ($this->stats['site_name'])
            {{ __('Site') }}: <span class="font-medium text-gray-900 dark:text-white">{{ $this->stats['site_name'] }}</span>
            ·
        @endif
        {{ __('Pay type') }}: <span class="font-medium text-gray-900 dark:text-white">{{ $this->stats['salary_type'] }}</span>
    </div>

    <div class="grid gap-4 md:grid-cols-3">
        <x-filament::section heading="{{ __('Cars Today') }}">
            <div class="text-3xl font-bold text-primary-600">{{ number_format($this->stats['cars_today']) }}</div>
            <div class="mt-1 text-sm text-gray-500">{{ __('Yesterday') }}: {{ number_format($this->stats['cars_yesterday']) }}</div>
        </x-filament::section>

        <x-filament::section heading="{{ __('Cars This Month') }}">
            <div class="text-3xl font-bold text-primary-600">{{ number_format($this->stats['cars_this_month']) }}</div>
        </x-filament::section>

        <x-filament::section heading="{{ __('Est. Earnings Today') }}">
            <div class="text-3xl font-bold text-success-600">{{ money_format_app($this->stats['estimated_earnings_today']) }}</div>
            <div class="mt-1 text-sm text-gray-500">
                {{ __('This month') }}: {{ money_format_app($this->stats['estimated_earnings_month']) }}
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
