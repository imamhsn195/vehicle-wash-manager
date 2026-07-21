<x-filament-widgets::widget>
    <x-filament::section heading="{{ static::$heading }}">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <th class="px-3 py-2 text-left font-medium text-gray-600 dark:text-gray-300">#</th>
                        <th class="px-3 py-2 text-left font-medium text-gray-600 dark:text-gray-300">{{ __('Staff') }}</th>
                        <th class="px-3 py-2 text-right font-medium text-gray-600 dark:text-gray-300">{{ __('Cars Washed') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($this->getStaff() as $index => $staff)
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <td class="px-3 py-2 text-gray-500">{{ $index + 1 }}</td>
                            <td class="px-3 py-2 font-medium">{{ $staff['name'] }}</td>
                            <td class="px-3 py-2 text-right">
                                <span class="inline-flex items-center rounded-full bg-primary-50 px-2 py-1 text-xs font-semibold text-primary-700 dark:bg-primary-400/10 dark:text-primary-400">
                                    {{ $staff['cars'] }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-3 py-6 text-center text-gray-500">{{ __('No wash data for today yet.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
