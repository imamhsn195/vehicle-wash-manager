<?php

namespace App\Filament\Pages;

use App\Models\Organization;
use App\Support\Currency;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class CurrencySettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static string $view = 'filament.pages.currency-settings';

    protected static ?string $navigationLabel = 'Currency';

    protected static ?string $title = 'Currency';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 98;

    public ?array $data = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public function mount(): void
    {
        $org = Currency::organization() ?? Organization::query()->first();

        $this->form->fill([
            'currency_code' => $org?->currency_code ?: Currency::code(),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('Business currency'))
                    ->description(__('Used for prices, payroll, expenses, and reports across the app.'))
                    ->schema([
                        Forms\Components\Select::make('currency_code')
                            ->label(__('Currency'))
                            ->options(Currency::options())
                            ->required()
                            ->searchable()
                            ->native(false)
                            ->helperText(fn (Forms\Get $get) => sprintf(
                                '%s · %s',
                                Currency::name($get('currency_code') ?: Currency::code()),
                                Currency::format(1250, $get('currency_code') ?: Currency::code())
                            )),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $code = strtoupper((string) $data['currency_code']);

        if (! array_key_exists($code, Currency::CATALOG)) {
            return;
        }

        $org = Currency::organization() ?? Organization::query()->first();

        if (! $org) {
            Notification::make()
                ->title(__('No organization found'))
                ->danger()
                ->send();

            return;
        }

        $org->update(['currency_code' => $code]);

        Notification::make()
            ->title(__('Currency updated'))
            ->body(Currency::options()[$code] ?? $code)
            ->success()
            ->send();
    }
}
