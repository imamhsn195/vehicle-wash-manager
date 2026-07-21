<?php

namespace App\Filament\Widgets;

use App\Services\ContractRenewalService;
use Filament\Widgets\Widget;

class AlertsWidget extends Widget
{
    protected static string $view = 'filament.widgets.alerts-widget';

    protected static ?string $heading = 'Alerts';

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = [
        'md' => 2,
        'xl' => 2,
    ];

    public function getRenewals(): array
    {
        return app(ContractRenewalService::class)
            ->upcomingRenewals()
            ->take(5)
            ->map(fn ($c) => [
                'title' => $c->title,
                'site' => $c->site?->name,
                'days' => $c->days_until_expiry,
                'overdue' => $c->is_overdue,
            ])
            ->all();
    }

    public function getMissingLogs(): array
    {
        return app(ContractRenewalService::class)
            ->sitesMissingTodayLog()
            ->map(fn ($s) => ['name' => $s->name, 'city' => $s->city])
            ->all();
    }
}
