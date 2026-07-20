<x-filament-panels::page>
    <form class="mb-6">
        {{ $this->form }}
    </form>

    @foreach ($this->results as $row)
        <x-filament::section :heading="$row['partner']" class="mb-4">
            <div class="mb-3 text-lg font-semibold">
                {{ __('Total Payout') }}:
                <span @class([
                    'text-success-600' => $row['total'] >= 0,
                    'text-danger-600' => $row['total'] < 0,
                ])>
                    ৳{{ number_format($row['total'], 0) }}
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <th class="px-3 py-2 text-left">{{ __('Site') }}</th>
                            <th class="px-3 py-2 text-right">{{ __('Share %') }}</th>
                            <th class="px-3 py-2 text-right">{{ __('Site Profit') }}</th>
                            <th class="px-3 py-2 text-right">{{ __('Payout') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($row['breakdown'] as $line)
                            <tr class="border-b border-gray-100 dark:border-gray-800">
                                <td class="px-3 py-2">{{ $line['site_name'] }}</td>
                                <td class="px-3 py-2 text-right">{{ number_format($line['share_pct'], 1) }}%</td>
                                <td class="px-3 py-2 text-right">৳{{ number_format($line['profit'], 0) }}</td>
                                <td class="px-3 py-2 text-right font-semibold">৳{{ number_format($line['payout'], 0) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-3 py-4 text-center text-gray-500">{{ __('No site shares assigned.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-filament::section>
    @endforeach
</x-filament-panels::page>
