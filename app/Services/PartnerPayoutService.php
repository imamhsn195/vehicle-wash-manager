<?php

namespace App\Services;

use App\Models\Partner;
use App\Models\PartnerSettlement;
use App\Models\PartnerSiteShare;
use Carbon\Carbon;

class PartnerPayoutService
{
    public function __construct(
        protected PnLService $pnlService
    ) {}

    public function calculatePartnerPayout(Partner $partner, int $year, int $month): float
    {
        return (float) collect($this->breakdown($partner, $year, $month))
            ->sum('payout');
    }

    /**
     * @return array<int, array{site_id: int, site_name: string, share_pct: float, profit: float, payout: float}>
     */
    public function breakdown(Partner $partner, int $year, int $month): array
    {
        return PartnerSiteShare::query()
            ->with('site')
            ->where('partner_id', $partner->id)
            ->get()
            ->map(function (PartnerSiteShare $share) use ($year, $month) {
                $pnl = $this->pnlService->siteMonthlyPnL($share->site, $year, $month);
                $payout = $pnl['profit'] * ((float) $share->share_pct / 100);

                return [
                    'site_id' => $share->site_id,
                    'site_name' => $share->site->name,
                    'share_pct' => (float) $share->share_pct,
                    'profit' => $pnl['profit'],
                    'payout' => $payout,
                ];
            })
            ->values()
            ->all();
    }

    public function createSettlement(Partner $partner, int $year, int $month): PartnerSettlement
    {
        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();
        $breakdown = $this->breakdown($partner, $year, $month);
        $amount = collect($breakdown)->sum('payout');

        return PartnerSettlement::create([
            'partner_id' => $partner->id,
            'period_start' => $start->toDateString(),
            'period_end' => $end->toDateString(),
            'amount' => $amount,
            'breakdown' => $breakdown,
        ]);
    }

    public function markPaid(PartnerSettlement $settlement): PartnerSettlement
    {
        $settlement->update(['paid_at' => now()]);

        return $settlement->fresh();
    }
}
