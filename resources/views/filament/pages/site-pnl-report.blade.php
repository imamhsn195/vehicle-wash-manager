<x-filament-panels::page>
    <form class="mb-6">
        {{ $this->form }}
    </form>

    <x-filament::section heading="{{ __('Monthly P&L by Site') }}">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <th class="px-3 py-2 text-left">{{ __('Site') }}</th>
                        <th class="px-3 py-2 text-right">{{ __('Cars') }}</th>
                        <th class="px-3 py-2 text-right">{{ __('Revenue') }}</th>
                        <th class="px-3 py-2 text-right">{{ __('Expenses') }}</th>
                        <th class="px-3 py-2 text-right">{{ __('Profit') }}</th>
                        <th class="px-3 py-2 text-right">{{ __('Margin') }}</th>
                        <th class="px-3 py-2 text-right">{{ __('Cost/Wash') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($this->results as $row)
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <td class="px-3 py-2 font-medium">{{ $row['site'] }}</td>
                            <td class="px-3 py-2 text-right">{{ number_format($row['cars']) }}</td>
                            <td class="px-3 py-2 text-right">{{ money_format_app($row['revenue']) }}</td>
                            <td class="px-3 py-2 text-right">{{ money_format_app($row['expenses']) }}</td>
                            <td @class([
                                'px-3 py-2 text-right font-semibold',
                                'text-success-600' => $row['profit'] >= 0,
                                'text-danger-600' => $row['profit'] < 0,
                            ])>
                                {{ money_format_app($row['profit']) }}
                            </td>
                            <td class="px-3 py-2 text-right">{{ number_format($row['margin_pct'], 1) }}%</td>
                            <td class="px-3 py-2 text-right">{{ money_format_app($row['cost_per_wash']) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-3 py-6 text-center text-gray-500">{{ __('No sites found.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-filament::section>
</x-filament-panels::page>
