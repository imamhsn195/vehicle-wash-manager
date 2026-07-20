<?php

namespace App\Filament\Widgets;

use App\Services\AnalyticsService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OverviewStats extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $analytics = app(AnalyticsService::class);

        return [
            Stat::make(__('Cars Today'), number_format($analytics->carsToday()))
                ->description(__('Across all sites'))
                ->descriptionIcon('heroicon-m-truck')
                ->color('success'),
            Stat::make(__('Revenue Today'), '৳'.number_format($analytics->revenueToday(), 0))
                ->description(__('From wash logs'))
                ->descriptionIcon('heroicon-m-banknotes')
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
}
