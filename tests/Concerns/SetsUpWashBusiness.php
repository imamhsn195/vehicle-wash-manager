<?php

namespace Tests\Concerns;

use App\Enums\PaymentMethod;
use App\Enums\Shift;
use App\Enums\UserRole;
use App\Models\Organization;
use App\Models\ServiceType;
use App\Models\Site;
use App\Models\Staff;
use App\Models\StaffAssignment;
use App\Models\User;

trait SetsUpWashBusiness
{
    protected Organization $organization;

    protected Site $site;

    protected ServiceType $serviceType;

    protected Staff $staff;

    protected User $admin;

    protected User $manager;

    protected function setUpWashBusiness(): void
    {
        $this->organization = Organization::create(['name' => 'Test Wash Co.']);

        $this->admin = User::factory()->create([
            'organization_id' => $this->organization->id,
            'role' => UserRole::Admin,
            'email' => 'admin@test.test',
        ]);

        $this->manager = User::factory()->create([
            'organization_id' => $this->organization->id,
            'role' => UserRole::SiteManager,
            'email' => 'manager@test.test',
        ]);

        $this->site = Site::create([
            'organization_id' => $this->organization->id,
            'manager_id' => $this->manager->id,
            'name' => 'Test Mall',
            'mall_name' => 'Test Mall',
            'city' => 'Dhaka',
            'is_active' => true,
        ]);

        $this->serviceType = ServiceType::create([
            'site_id' => $this->site->id,
            'name' => 'Standard Wash',
            'price' => 200,
            'is_active' => true,
        ]);

        $this->staff = Staff::create([
            'organization_id' => $this->organization->id,
            'name' => 'Test Washer',
            'staff_type' => 'washer',
            'salary_type' => 'daily',
            'is_active' => true,
        ]);

        StaffAssignment::create([
            'staff_id' => $this->staff->id,
            'site_id' => $this->site->id,
            'is_primary' => true,
            'start_date' => now()->toDateString(),
        ]);
    }

    protected function validEntryData(array $overrides = []): array
    {
        return array_merge([
            'site_id' => $this->site->id,
            'date' => now()->toDateString(),
            'shift' => Shift::Morning->value,
            'staff_id' => $this->staff->id,
            'vehicle_count' => 5,
            'payment_method' => PaymentMethod::Cash->value,
        ], $overrides);
    }
}
