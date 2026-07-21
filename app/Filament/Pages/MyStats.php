<?php

namespace App\Filament\Pages;

use App\Enums\UserRole;
use App\Services\StaffPortalService;
use Filament\Pages\Page;

class MyStats extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-pie';

    protected static string $view = 'filament.pages.my-stats';

    protected static ?string $navigationLabel = 'My Stats';

    protected static ?string $title = 'My Performance';

    protected static ?string $navigationGroup = 'My Work';

    protected static ?int $navigationSort = 0;

    public array $stats = [];

    public static function canAccess(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        if ($user->role === UserRole::Staff) {
            return true;
        }

        return app(StaffPortalService::class)->staffForUser($user) !== null;
    }

    public function mount(): void
    {
        $staff = app(StaffPortalService::class)->staffForUser(auth()->user());

        $this->stats = $staff
            ? app(StaffPortalService::class)->statsFor($staff)
            : [
                'cars_today' => 0,
                'cars_yesterday' => 0,
                'cars_this_month' => 0,
                'estimated_earnings_today' => 0,
                'estimated_earnings_month' => 0,
                'salary_type' => '—',
                'site_name' => null,
            ];
    }
}
