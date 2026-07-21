<?php

namespace App\Filament\Widgets;

use App\Services\AnalyticsService;
use Filament\Widgets\Widget;

class StaffProductivityToday extends Widget
{
    protected static string $view = 'filament.widgets.staff-productivity-today';

    protected static ?string $heading = 'Staff Productivity Today';

    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = [
        'md' => 2,
        'xl' => 2,
    ];

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
