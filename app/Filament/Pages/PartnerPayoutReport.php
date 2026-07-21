<?php

namespace App\Filament\Pages;

use App\Models\Partner;
use App\Services\PartnerPayoutService;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;

class PartnerPayoutReport extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static string $view = 'filament.pages.partner-payout-report';

    protected static ?string $navigationLabel = 'Partner Payouts';

    protected static ?string $title = 'Partner Payout Report';

    protected static ?string $navigationGroup = 'Partners & Payroll';

    protected static ?int $navigationSort = 0;

    public ?array $data = [];

    public array $results = [];

    public function mount(): void
    {
        $this->form->fill([
            'year' => now()->year,
            'month' => now()->month,
        ]);

        $this->generate();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('year')
                    ->options(collect(range(now()->year - 2, now()->year))->mapWithKeys(fn ($y) => [$y => $y]))
                    ->required()
                    ->live(),
                Forms\Components\Select::make('month')
                    ->options(collect(range(1, 12))->mapWithKeys(fn ($m) => [$m => now()->month($m)->format('F')]))
                    ->required()
                    ->live(),
            ])
            ->columns(2)
            ->statePath('data');
    }

    public function generate(): void
    {
        $year = (int) ($this->data['year'] ?? now()->year);
        $month = (int) ($this->data['month'] ?? now()->month);
        $service = app(PartnerPayoutService::class);

        $this->results = Partner::query()
            ->where('is_active', true)
            ->get()
            ->map(function (Partner $partner) use ($service, $year, $month) {
                $breakdown = $service->breakdown($partner, $year, $month);

                return [
                    'partner' => $partner->name,
                    'total' => collect($breakdown)->sum('payout'),
                    'breakdown' => $breakdown,
                ];
            })
            ->all();
    }

    public function updatedData(): void
    {
        $this->generate();
    }
}
