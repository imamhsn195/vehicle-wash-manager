<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Mail\DailySummaryMail;
use App\Models\Organization;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class DailySummaryService
{
    public function __construct(
        protected AnalyticsService $analytics,
        protected ?ContractRenewalService $renewals = null
    ) {
        $this->renewals ??= app(ContractRenewalService::class);
    }

    /**
     * @return array{
     *   date: string,
     *   total_cars: int,
     *   total_revenue: float,
     *   by_site: array,
     *   missing_sites: array
     * }
     */
    public function build(?Carbon $date = null): array
    {
        $date ??= today();

        // Temporarily use "today" helpers by freezing date via wash query on that date
        $bySite = $this->analytics->revenueBySiteOnDate($date);
        $totalCars = (int) $bySite->sum('cars');
        $totalRevenue = (float) $bySite->sum('revenue');

        $missing = $this->renewals
            ->sitesMissingTodayLog($date)
            ->pluck('name')
            ->all();

        return [
            'date' => $date->toDateString(),
            'total_cars' => $totalCars,
            'total_revenue' => $totalRevenue,
            'by_site' => $bySite->map(fn ($row) => [
                'site_name' => $row->site_name,
                'cars' => (int) $row->cars,
                'revenue' => (float) $row->revenue,
            ])->all(),
            'missing_sites' => $missing,
        ];
    }

    public function sendToAdmins(Organization $organization, ?Carbon $date = null): int
    {
        $summary = $this->build($date);
        $admins = User::query()
            ->where('organization_id', $organization->id)
            ->where('role', UserRole::Admin)
            ->where('is_active', true)
            ->get();

        foreach ($admins as $admin) {
            Mail::to($admin->email)->send(new DailySummaryMail($summary));
        }

        return $admins->count();
    }
}
