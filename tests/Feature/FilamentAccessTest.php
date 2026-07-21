<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\SetsUpWashBusiness;
use Tests\TestCase;

class FilamentAccessTest extends TestCase
{
    use SetsUpWashBusiness;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpWashBusiness();
    }

    public function test_guest_is_redirected_from_admin_panel(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
    }

    public function test_admin_can_access_dashboard(): void
    {
        $this->actingAs($this->admin)
            ->get('/admin')
            ->assertOk();
    }

    public function test_site_manager_can_access_dashboard(): void
    {
        $this->actingAs($this->manager)
            ->get('/admin')
            ->assertOk();
    }

    public function test_inactive_user_cannot_access_panel(): void
    {
        $inactive = User::factory()->create([
            'organization_id' => $this->organization->id,
            'role' => UserRole::Staff,
            'is_active' => false,
        ]);

        $this->actingAs($inactive)
            ->get('/admin')
            ->assertForbidden();
    }
}
