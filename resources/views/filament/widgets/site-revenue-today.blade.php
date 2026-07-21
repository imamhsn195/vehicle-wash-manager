<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            {{ __('Daily Revenue by Site') }}
        </x-slot>
        <x-slot name="description">
            {{ __('Latest activity across mall locations') }}
        </x-slot>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-white/10">
                        <th class="px-3 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Site') }}</th>
                        <th class="px-3 py-2.5 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Cars') }}</th>
                        <th class="px-3 py-2.5 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Revenue') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                    @forelse ($this->getSites() as $site)
                        <tr class="transition hover:bg-slate-50/80 dark:hover:bg-white/5">
                            <td class="px-3 py-3">
                                <div class="flex items-center gap-3">
                                    <span class="flex h-9 w-9 items-center justify-center rounded-full bg-primary-50 text-xs font-bold text-primary-700 dark:bg-primary-400/10 dark:text-primary-300">
                                        {{ \Illuminate\Support\Str::substr($site['site_name'], 0, 1) }}
                                    </span>
                                    <span class="font-semibold text-slate-900 dark:text-white">{{ $site['site_name'] }}</span>
                                </div>
                            </td>
                            <td class="px-3 py-3 text-right text-slate-600 dark:text-slate-300">{{ number_format($site['cars']) }}</td>
                            <td class="px-3 py-3 text-right font-semibold text-primary-700 dark:text-primary-300">{{ money_format_app($site['revenue']) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-3 py-10 text-center text-slate-500">{{ __('No revenue data for today yet.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
