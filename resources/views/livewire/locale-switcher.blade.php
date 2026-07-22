<x-filament::dropdown
    placement="bottom-end"
    teleport
    wire:key="locale-switcher-{{ $current }}"
>
    <x-slot name="trigger">
        <button
            type="button"
            class="fi-icon-btn relative flex items-center justify-center gap-x-1.5 rounded-lg px-2.5 py-1.5 text-sm font-semibold outline-none transition duration-75 hover:bg-gray-400/10 focus-visible:bg-gray-400/10 dark:hover:bg-white/5 dark:focus-visible:bg-white/5 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200"
            aria-label="{{ __('Language') }}"
            title="{{ __('Language') }}"
        >
            <x-filament::icon
                icon="heroicon-m-language"
                class="h-5 w-5"
            />
            <span class="hidden sm:inline text-xs font-bold uppercase tracking-wide">
                {{ $current }}
            </span>
            <x-filament::icon
                icon="heroicon-m-chevron-down"
                class="h-3.5 w-3.5 opacity-70"
            />
        </button>
    </x-slot>

    <x-filament::dropdown.header>
        {{ __('Language') }}
    </x-filament::dropdown.header>

    <x-filament::dropdown.list>
        @foreach ($locales as $code)
            @php
                $active = $current === $code;
            @endphp
            <x-filament::dropdown.list.item
                wire:click="setLocale('{{ $code }}')"
                wire:loading.attr="disabled"
                :color="$active ? 'primary' : 'gray'"
                :icon="$active ? 'heroicon-m-check' : null"
            >
                <span class="flex min-w-0 flex-col text-start">
                    <span class="font-medium leading-tight">
                        {{ \App\Http\Middleware\SetLocale::label($code) }}
                    </span>
                    <span class="text-xs text-gray-400 dark:text-gray-500">
                        {{ strtoupper($code) }} · {{ \App\Http\Middleware\SetLocale::hint($code) }}
                    </span>
                </span>
            </x-filament::dropdown.list.item>
        @endforeach
    </x-filament::dropdown.list>
</x-filament::dropdown>
