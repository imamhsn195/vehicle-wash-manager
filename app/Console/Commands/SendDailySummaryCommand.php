<?php

namespace App\Console\Commands;

use App\Models\Organization;
use App\Services\DailySummaryService;
use Illuminate\Console\Command;

class SendDailySummaryCommand extends Command
{
    protected $signature = 'wash:daily-summary {--date=}';

    protected $description = 'Email daily wash summary to organization admins';

    public function handle(DailySummaryService $service): int
    {
        $date = $this->option('date')
            ? \Illuminate\Support\Carbon::parse($this->option('date'))
            : today();

        $total = 0;

        foreach (Organization::all() as $org) {
            $sent = $service->sendToAdmins($org, $date);
            $total += $sent;
            $this->info("{$org->name}: sent to {$sent} admin(s)");
        }

        $this->info("Done. {$total} email(s) sent for {$date->toDateString()}.");

        return self::SUCCESS;
    }
}
