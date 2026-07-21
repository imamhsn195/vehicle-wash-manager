<x-filament-widgets::widget>
    <div class="grid gap-6 lg:grid-cols-2">
        <x-filament::section>
            <x-slot name="heading">
                {{ __('Contract Renewals') }}
            </x-slot>
            <x-slot name="description">
                {{ __('Upcoming lease deadlines') }}
            </x-slot>

            <ul class="divide-y divide-slate-100 dark:divide-white/5">
                @forelse ($this->getRenewals() as $alert)
                    <li class="flex items-center justify-between gap-3 py-3 first:pt-0 last:pb-0">
                        <div class="min-w-0">
                            <div class="truncate text-sm font-semibold text-slate-900 dark:text-white">{{ $alert['title'] }}</div>
                            <div class="truncate text-xs text-slate-500">{{ $alert['site'] }}</div>
                        </div>
                        <span @class([
                            'shrink-0 rounded-full px-2.5 py-1 text-xs font-semibold',
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
                    <li class="py-8 text-center text-sm text-slate-500">{{ __('No renewals due soon.') }}</li>
                @endforelse
            </ul>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">
                {{ __('Missing Daily Logs') }}
            </x-slot>
            <x-slot name="description">
                {{ __('Sites with no wash entry today') }}
            </x-slot>

            <ul class="divide-y divide-slate-100 dark:divide-white/5">
                @forelse ($this->getMissingLogs() as $site)
                    <li class="flex items-center justify-between gap-3 py-3 first:pt-0 last:pb-0">
                        <div class="flex min-w-0 items-center gap-3">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-slate-100 text-xs font-bold uppercase text-slate-600 dark:bg-white/10 dark:text-slate-300">
                                {{ \Illuminate\Support\Str::substr($site['name'], 0, 1) }}
                            </span>
                            <div class="min-w-0">
                                <div class="truncate text-sm font-semibold text-slate-900 dark:text-white">{{ $site['name'] }}</div>
                                <div class="truncate text-xs text-slate-500">{{ $site['city'] }}</div>
                            </div>
                        </div>
                        <span class="shrink-0 rounded-full bg-warning-50 px-2.5 py-1 text-xs font-semibold text-warning-700 dark:bg-warning-400/10 dark:text-warning-400">
                            {{ __('No log today') }}
                        </span>
                    </li>
                @empty
                    <li class="py-8 text-center text-sm text-slate-500">{{ __('All sites logged today.') }}</li>
                @endforelse
            </ul>
        </x-filament::section>
    </div>
</x-filament-widgets::widget>
