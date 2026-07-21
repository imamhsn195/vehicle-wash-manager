<x-filament-panels::page>
    <form wire:submit="save" class="max-w-md space-y-4">
        <x-filament::section heading="{{ __('Language') }}">
            <x-filament::input.wrapper>
                <select wire:model="locale" class="fi-input block w-full border-none bg-transparent py-1.5 text-base text-gray-950 outline-none dark:text-white sm:text-sm sm:leading-6">
                    @foreach ($this->localeOptions() as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </x-filament::input.wrapper>

            <div class="mt-4">
                <x-filament::button type="submit">
                    {{ __('Save') }}
                </x-filament::button>
            </div>
        </x-filament::section>
    </form>
</x-filament-panels::page>
