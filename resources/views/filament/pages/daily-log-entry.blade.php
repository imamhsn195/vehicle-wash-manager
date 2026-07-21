<x-filament-panels::page>
    <form wire:submit="submit" class="space-y-6">
        {{ $this->form }}

        <x-filament::button type="submit" size="lg" class="w-full">
            {{ __('Save Wash Entry') }}
        </x-filament::button>
    </form>

    <x-filament::section class="mt-6" heading="{{ __('Tips') }}">
        <ul class="list-disc space-y-1 pl-5 text-sm text-gray-600 dark:text-gray-400">
            <li>{{ __('Select site, shift, and staff, then enter the number of cars washed.') }}</li>
            <li>{{ __('You can submit multiple entries — one per staff member.') }}</li>
            <li>{{ __('Data appears on the dashboard immediately.') }}</li>
        </ul>
    </x-filament::section>
</x-filament-panels::page>
