<?php

namespace App\Filament\Widgets;

use App\Services\AnalyticsService;
use Filament\Widgets\Widget;

class SiteRevenueToday extends Widget
{
    protected static string $view = 'filament.widgets.site-revenue-today';

    protected static ?string $heading = 'Daily Revenue by Site';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public function getSites(): array
    {
        return app(AnalyticsService::class)
            ->revenueBySiteToday()
            ->map(fn ($row) => [
                'site_name' => $row->site_name,
                'cars' => (int) $row->cars,
                'revenue' => (float) $row->revenue,
            ])
            ->all();
    }
}
