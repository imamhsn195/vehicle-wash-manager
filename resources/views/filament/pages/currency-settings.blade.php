<x-filament-panels::page>
    <form wire:submit="save" class="mx-auto max-w-xl space-y-6">
        {{ $this->form }}

        <div class="flex items-center justify-between gap-3 rounded-xl border border-gray-200 bg-white px-4 py-3 dark:border-white/10 dark:bg-gray-900">
            <div>
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">
                    {{ __('Preview') }}
                </div>
                <div class="mt-1 text-2xl font-bold text-amber-600 dark:text-amber-300">
                    {{ money_format_app(1250, $this->data['currency_code'] ?? currency_code()) }}
                </div>
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    {{ currency_code() }} · {{ \App\Support\Currency::name($this->data['currency_code'] ?? null) }}
                </div>
            </div>
            <x-filament::button type="submit" color="primary" icon="heroicon-m-check">
                {{ __('Save') }}
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
