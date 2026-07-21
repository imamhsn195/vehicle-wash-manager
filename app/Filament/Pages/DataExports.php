<?php

namespace App\Filament\Pages;

use App\Services\ExportService;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Pages\Page;

class DataExports extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-arrow-down-tray';

    protected static string $view = 'filament.pages.data-exports';

    protected static ?string $navigationLabel = 'Exports';

    protected static ?string $title = 'Data Exports';

    protected static ?string $navigationGroup = 'Finance';

    protected static ?int $navigationSort = 5;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportDailyLogs')
                ->label('Daily Logs')
                ->icon('heroicon-o-clipboard-document-list')
                ->form([
                    Forms\Components\DatePicker::make('from')->default(now()->startOfMonth())->required(),
                    Forms\Components\DatePicker::make('to')->default(now())->required(),
                ])
                ->action(fn (array $data) => app(ExportService::class)->downloadDailyLogs(
                    \Illuminate\Support\Carbon::parse($data['from']),
                    \Illuminate\Support\Carbon::parse($data['to'])
                )),
            Action::make('exportExpenses')
                ->label('Expenses')
                ->icon('heroicon-o-banknotes')
                ->form([
                    Forms\Components\DatePicker::make('from')->default(now()->startOfMonth())->required(),
                    Forms\Components\DatePicker::make('to')->default(now())->required(),
                ])
                ->action(fn (array $data) => app(ExportService::class)->downloadExpenses(
                    \Illuminate\Support\Carbon::parse($data['from']),
                    \Illuminate\Support\Carbon::parse($data['to'])
                )),
            Action::make('exportPnL')
                ->label('Site P&L')
                ->icon('heroicon-o-chart-bar')
                ->form([
                    Forms\Components\Select::make('year')
                        ->options(collect(range(now()->year - 2, now()->year))->mapWithKeys(fn ($y) => [$y => $y]))
                        ->default(now()->year)
                        ->required(),
                    Forms\Components\Select::make('month')
                        ->options(collect(range(1, 12))->mapWithKeys(fn ($m) => [$m => now()->month($m)->format('F')]))
                        ->default(now()->month)
                        ->required(),
                ])
                ->action(fn (array $data) => app(ExportService::class)->downloadSitePnL(
                    (int) $data['year'],
                    (int) $data['month']
                )),
        ];
    }
}
