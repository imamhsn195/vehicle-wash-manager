<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Filament\Pages\MyStats;
use App\Models\User;
use Livewire\Livewire;
use Tests\Concerns\SetsUpWashBusiness;
use Tests\TestCase;

class MyStatsPageTest extends TestCase
{
    use SetsUpWashBusiness;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpWashBusiness();
    }

    public function test_staff_user_can_view_my_stats(): void
    {
        $staffUser = User::factory()->create([
            'organization_id' => $this->organization->id,
            'role' => UserRole::Staff,
            'email' => 'washer@test.test',
        ]);

        $this->staff->update([
            'user_id' => $staffUser->id,
            'salary_type' => 'per_car',
            'per_wash_rate' => 15,
        ]);

        Livewire::actingAs($staffUser)
            ->test(MyStats::class)
            ->assertOk()
            ->assertSee('My Performance');
    }

    public function test_admin_without_staff_profile_cannot_access_my_stats(): void
    {
        $this->assertFalse(MyStats::canAccess());

        $this->actingAs($this->admin);
        // canAccess uses auth()->user()
        $this->assertFalse(MyStats::canAccess());
    }

    public function test_manager_with_linked_staff_profile_can_access(): void
    {
        $this->staff->update(['user_id' => $this->manager->id]);

        $this->actingAs($this->manager);
        $this->assertTrue(MyStats::canAccess());
    }
}
