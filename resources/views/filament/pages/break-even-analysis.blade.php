<x-filament-panels::page>
    <form class="mb-6">
        {{ $this->form }}
    </form>

    <x-filament::section heading="{{ __('Cars Needed Per Day to Break Even') }}">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <th class="px-3 py-2 text-left">{{ __('Site') }}</th>
                        <th class="px-3 py-2 text-right">{{ __('Avg Price') }}</th>
                        <th class="px-3 py-2 text-right">{{ __('Daily Fixed Cost') }}</th>
                        <th class="px-3 py-2 text-right">{{ __('Break-Even Cars/Day') }}</th>
                        <th class="px-3 py-2 text-right">{{ __('Avg Cars/Day') }}</th>
                        <th class="px-3 py-2 text-center">{{ __('Status') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($this->results as $row)
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <td class="px-3 py-2 font-medium">{{ $row['site'] }}</td>
                            <td class="px-3 py-2 text-right">৳{{ number_format($row['avg_price'], 0) }}</td>
                            <td class="px-3 py-2 text-right">৳{{ number_format($row['daily_fixed_cost'], 0) }}</td>
                            <td class="px-3 py-2 text-right font-semibold">
                                {{ $row['break_even_cars'] ?? '∞' }}
                            </td>
                            <td class="px-3 py-2 text-right">{{ number_format($row['avg_daily_cars'], 1) }}</td>
                            <td class="px-3 py-2 text-center">
                                @if ($row['is_below_break_even'])
                                    <span class="inline-flex rounded-full bg-danger-50 px-2 py-1 text-xs font-semibold text-danger-700 dark:bg-danger-400/10 dark:text-danger-400">
                                        {{ __('Below') }}
                                    </span>
                                @else
                                    <span class="inline-flex rounded-full bg-success-50 px-2 py-1 text-xs font-semibold text-success-700 dark:bg-success-400/10 dark:text-success-400">
                                        {{ __('OK') }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-3 py-6 text-center text-gray-500">{{ __('No sites found.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-filament::section>
</x-filament-panels::page>
