<?php

namespace Tests\Unit;

use App\Services\AnalyticsService;
use Tests\Concerns\SetsUpWashBusiness;
use Tests\TestCase;

class AnalyticsTrendTest extends TestCase
{
    use SetsUpWashBusiness;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpWashBusiness();
    }

    public function test_percent_change_and_series(): void
    {
        $service = app(AnalyticsService::class);

        $this->assertEquals(100.0, $service->percentChange(10, 0));
        $this->assertEquals(0.0, $service->percentChange(0, 0));
        $this->assertEquals(50.0, $service->percentChange(150, 100));

        $series = $service->revenueAndCarsLastDays(7);
        $this->assertCount(7, $series['labels']);
        $this->assertCount(7, $series['revenue']);
        $this->assertCount(7, $series['cars']);
    }
}
