<?php

namespace App\Filament\Widgets;

use App\Services\AnalyticsService;
use Filament\Widgets\Widget;

class StaffProductivityToday extends Widget
{
    protected static string $view = 'filament.widgets.staff-productivity-today';

    protected static ?string $heading = 'Staff Productivity Today';

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    public function getStaff(): array
    {
        return app(AnalyticsService::class)
            ->staffProductivityToday()
            ->map(fn ($row) => [
                'name' => $row->name,
                'cars' => (int) $row->cars,
            ])
            ->all();
    }
}
