<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\AlertsWidget;
use App\Filament\Widgets\OverviewStats;
use App\Filament\Widgets\RevenueChart;
use App\Filament\Widgets\SiteRevenueToday;
use App\Filament\Widgets\StaffProductivityToday;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    public function getColumns(): int|string|array
    {
        return [
            'md' => 2,
            'xl' => 4,
        ];
    }

    public function getWidgets(): array
    {
        return [
            OverviewStats::class,
            RevenueChart::class,
            AlertsWidget::class,
            SiteRevenueToday::class,
            StaffProductivityToday::class,
        ];
    }

    public function getHeading(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return __('Dashboard');
    }

    public function getSubheading(): string|\Illuminate\Contracts\Support\Htmlable|null
    {
        $name = auth()->user()?->name;

        return $name
            ? __('Welcome back, :name', ['name' => $name])
            : __('Welcome back to Vehicle Wash Manager');
    }
}
