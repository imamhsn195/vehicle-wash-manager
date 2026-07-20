<?php

namespace App\Filament\Pages;

use App\Services\BreakEvenService;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;

class BreakEvenAnalysis extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-calculator';

    protected static string $view = 'filament.pages.break-even-analysis';

    protected static ?string $navigationLabel = 'Break-Even';

    protected static ?string $title = 'Break-Even Analysis';

    protected static ?string $navigationGroup = 'Finance';

    protected static ?int $navigationSort = 4;

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
        $this->results = app(BreakEvenService::class)->allSites($year, $month);
    }

    public function updatedData(): void
    {
        $this->generate();
    }
}
