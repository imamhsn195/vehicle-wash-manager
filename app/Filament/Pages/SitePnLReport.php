<?php

namespace App\Filament\Pages;

use App\Models\Site;
use App\Services\PnLService;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;

class SitePnLReport extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static string $view = 'filament.pages.site-pnl-report';

    protected static ?string $navigationLabel = 'Site P&L';

    protected static ?string $title = 'Monthly Site P&L';

    protected static ?string $navigationGroup = 'Finance';

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
        $pnl = app(PnLService::class);

        $this->results = Site::query()
            ->where('is_active', true)
            ->get()
            ->map(function (Site $site) use ($pnl, $year, $month) {
                return [
                    'site' => $site->name,
                    ...$pnl->siteMonthlyPnL($site, $year, $month),
                ];
            })
            ->all();
    }

    public function updatedData(): void
    {
        $this->generate();
    }
}
