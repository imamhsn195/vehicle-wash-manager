<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            {{ __('Staff Productivity') }}
        </x-slot>
        <x-slot name="description">
            {{ __('Top washers by cars today') }}
        </x-slot>

        <div class="overflow-x-auto">
            <table class="w-full min-w-0 table-fixed text-sm sm:table-auto">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-white/10">
                        <th class="w-10 px-2 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 sm:px-3">#</th>
                        <th class="px-2 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 sm:px-3">{{ __('Staff') }}</th>
                        <th class="w-24 px-2 py-2.5 text-right text-xs font-semibold uppercase tracking-wide text-slate-500 sm:w-auto sm:px-3">{{ __('Cars Washed') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                    @forelse ($this->getStaff() as $index => $staff)
                        <tr class="transition hover:bg-slate-50/80 dark:hover:bg-white/5">
                            <td class="px-2 py-3 text-slate-400 sm:px-3">{{ $index + 1 }}</td>
                            <td class="max-w-0 px-2 py-3 sm:px-3">
                                <div class="flex min-w-0 items-center gap-2 sm:gap-3">
                                    <span class="hidden h-9 w-9 shrink-0 items-center justify-center rounded-full bg-slate-100 text-xs font-bold text-slate-600 sm:flex dark:bg-white/10 dark:text-slate-300">
                                        {{ \Illuminate\Support\Str::substr($staff['name'], 0, 1) }}
                                    </span>
                                    <span class="truncate font-semibold text-slate-900 dark:text-white">{{ $staff['name'] }}</span>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-2 py-3 text-right sm:px-3">
                                <span class="inline-flex items-center rounded-full bg-primary-50 px-2.5 py-1 text-xs font-semibold text-primary-700 dark:bg-primary-400/10 dark:text-primary-300">
                                    {{ $staff['cars'] }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-3 py-10 text-center text-slate-500">{{ __('No wash data for today yet.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
