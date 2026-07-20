<x-filament-widgets::widget>
    <div class="grid gap-4 md:grid-cols-2">
        <x-filament::section heading="{{ __('Contract Renewals') }}">
            <ul class="space-y-2 text-sm">
                @forelse ($this->getRenewals() as $alert)
                    <li class="flex items-center justify-between border-b border-gray-100 py-2 dark:border-gray-800">
                        <div>
                            <div class="font-medium">{{ $alert['title'] }}</div>
                            <div class="text-gray-500">{{ $alert['site'] }}</div>
                        </div>
                        <span @class([
                            'rounded-full px-2 py-1 text-xs font-semibold',
                            'bg-danger-50 text-danger-700 dark:bg-danger-400/10 dark:text-danger-400' => $alert['overdue'] || $alert['days'] <= 7,
                            'bg-warning-50 text-warning-700 dark:bg-warning-400/10 dark:text-warning-400' => ! $alert['overdue'] && $alert['days'] > 7,
                        ])>
                            @if ($alert['overdue'])
                                {{ __('Overdue') }} ({{ abs($alert['days']) }}d)
                            @else
                                {{ $alert['days'] }}d
                            @endif
                        </span>
                    </li>
                @empty
                    <li class="py-4 text-center text-gray-500">{{ __('No renewals due soon.') }}</li>
                @endforelse
            </ul>
        </x-filament::section>

        <x-filament::section heading="{{ __('Missing Daily Logs') }}">
            <ul class="space-y-2 text-sm">
                @forelse ($this->getMissingLogs() as $site)
                    <li class="flex items-center justify-between border-b border-gray-100 py-2 dark:border-gray-800">
                        <div>
                            <div class="font-medium">{{ $site['name'] }}</div>
                            <div class="text-gray-500">{{ $site['city'] }}</div>
                        </div>
                        <span class="rounded-full bg-warning-50 px-2 py-1 text-xs font-semibold text-warning-700 dark:bg-warning-400/10 dark:text-warning-400">
                            {{ __('No log today') }}
                        </span>
                    </li>
                @empty
                    <li class="py-4 text-center text-gray-500">{{ __('All sites logged today.') }}</li>
                @endforelse
            </ul>
        </x-filament::section>
    </div>
</x-filament-widgets::widget>
