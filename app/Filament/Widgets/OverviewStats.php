<?php

namespace App\Filament\Widgets;

use App\Services\AnalyticsService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OverviewStats extends BaseWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $analytics = app(AnalyticsService::class);

        $carsToday = $analytics->carsToday();
        $carsYesterday = $analytics->carsOnDate(today()->subDay());
        $carsChange = $analytics->percentChange($carsToday, $carsYesterday);

        $revenueToday = $analytics->revenueToday();
        $revenueYesterday = $analytics->revenueOnDate(today()->subDay());
        $revenueChange = $analytics->percentChange($revenueToday, $revenueYesterday);

        $series = $analytics->revenueAndCarsLastDays(7);

        return [
            Stat::make(__('Cars Today'), number_format($carsToday))
                ->description($this->trendLabel($carsChange, __('vs yesterday')))
                ->descriptionIcon($carsChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->chart($series['cars'])
                ->color($carsChange >= 0 ? 'success' : 'danger'),
            Stat::make(__('Revenue Today'), money_format_app($revenueToday))
                ->description($this->trendLabel($revenueChange, __('vs yesterday')))
                ->descriptionIcon($revenueChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->chart($series['revenue'])
                ->color('primary'),
            Stat::make(__('Active Sites'), (string) \App\Models\Site::where('is_active', true)->count())
                ->description(__('Mall locations'))
                ->descriptionIcon('heroicon-m-building-storefront')
                ->color('warning'),
            Stat::make(__('Active Staff'), (string) \App\Models\Staff::where('is_active', true)->count())
                ->description(__('Washers & supervisors'))
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),
        ];
    }

    protected function trendLabel(float $change, string $suffix): string
    {
        $prefix = $change >= 0 ? '+' : '';

        return sprintf('%s%s%% %s', $prefix, number_format($change, 1), $suffix);
    }
}
