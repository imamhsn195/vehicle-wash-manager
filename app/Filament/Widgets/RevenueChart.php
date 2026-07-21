<?php

namespace App\Filament\Widgets;

use App\Services\AnalyticsService;
use Filament\Widgets\ChartWidget;

class RevenueChart extends ChartWidget
{
    protected static ?string $heading = null;

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = [
        'md' => 2,
        'xl' => 2,
    ];

    protected static ?string $maxHeight = '280px';

    public function getHeading(): string
    {
        return __('Revenue Stream');
    }

    public function getDescription(): ?string
    {
        return __('Daily performance & wash volume (last 7 days)');
    }

    protected function getData(): array
    {
        $series = app(AnalyticsService::class)->revenueAndCarsLastDays(7);

        return [
            'datasets' => [
                [
                    'label' => __('Revenue'),
                    'data' => $series['revenue'],
                    'borderColor' => 'rgb(13, 148, 136)',
                    'backgroundColor' => 'rgba(13, 148, 136, 0.12)',
                    'fill' => true,
                    'tension' => 0.35,
                    'yAxisID' => 'y',
                ],
                [
                    'label' => __('Cars'),
                    'data' => $series['cars'],
                    'borderColor' => 'rgb(245, 158, 11)',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.08)',
                    'fill' => false,
                    'tension' => 0.35,
                    'yAxisID' => 'y1',
                ],
            ],
            'labels' => $series['labels'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
            ],
            'scales' => [
                'y' => [
                    'position' => 'left',
                    'grid' => [
                        'color' => 'rgba(148, 163, 184, 0.2)',
                    ],
                ],
                'y1' => [
                    'position' => 'right',
                    'grid' => [
                        'drawOnChartArea' => false,
                    ],
                ],
            ],
        ];
    }
}
