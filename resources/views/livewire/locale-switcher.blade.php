<div
    x-data="{ open: false }"
    class="relative"
    wire:key="locale-switcher-{{ $current }}"
>
    <button
        type="button"
        @click="open = ! open"
        @keydown.escape.window="open = false"
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

    <div
        x-cloak
        x-show="open"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        @click.outside="open = false"
        class="fi-dropdown-panel absolute end-0 z-50 mt-2 w-56 origin-top-right rounded-xl bg-white p-1.5 shadow-lg ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10"
        style="display: none;"
    >
        <div class="px-2.5 py-1.5 text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">
            {{ __('Language') }}
        </div>

        <ul class="flex flex-col gap-0.5" role="listbox" aria-label="{{ __('Language') }}">
            @foreach ($locales as $code)
                @php
                    $active = $current === $code;
                @endphp
                <li>
                    <button
                        type="button"
                        wire:click="setLocale('{{ $code }}')"
                        wire:loading.attr="disabled"
                        @click="open = false"
                        role="option"
                        @if ($active) aria-selected="true" @endif
                        class="flex w-full items-center gap-3 rounded-lg px-2.5 py-2 text-start text-sm transition
                            {{ $active
                                ? 'bg-amber-500/15 text-amber-700 dark:bg-amber-400/15 dark:text-amber-300'
                                : 'text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-white/5' }}"
                    >
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md bg-gray-100 text-[10px] font-bold uppercase tracking-wide text-gray-600 dark:bg-white/10 dark:text-gray-300">
                            {{ $code }}
                        </span>
                        <span class="flex min-w-0 flex-1 flex-col">
                            <span class="truncate font-medium leading-tight">
                                {{ \App\Http\Middleware\SetLocale::label($code) }}
                            </span>
                            <span class="truncate text-xs text-gray-400 dark:text-gray-500">
                                {{ \App\Http\Middleware\SetLocale::hint($code) }}
                            </span>
                        </span>
                        @if ($active)
                            <x-filament::icon
                                icon="heroicon-m-check"
                                class="h-4 w-4 shrink-0 text-amber-600 dark:text-amber-300"
                            />
                        @endif
                    </button>
                </li>
            @endforeach
        </ul>
    </div>
</div>
