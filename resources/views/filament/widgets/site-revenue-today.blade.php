<x-filament-widgets::widget>
    <x-filament::section heading="{{ static::$heading }}">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <th class="px-3 py-2 text-left font-medium text-gray-600 dark:text-gray-300">{{ __('Site') }}</th>
                        <th class="px-3 py-2 text-right font-medium text-gray-600 dark:text-gray-300">{{ __('Cars') }}</th>
                        <th class="px-3 py-2 text-right font-medium text-gray-600 dark:text-gray-300">{{ __('Revenue') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($this->getSites() as $site)
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <td class="px-3 py-2 font-medium">{{ $site['site_name'] }}</td>
                            <td class="px-3 py-2 text-right">{{ number_format($site['cars']) }}</td>
                            <td class="px-3 py-2 text-right font-semibold text-primary-600">{{ money_format_app($site['revenue']) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-3 py-6 text-center text-gray-500">{{ __('No revenue data for today yet.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
