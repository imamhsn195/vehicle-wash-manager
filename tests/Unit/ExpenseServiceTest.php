<?php

namespace Tests\Unit;

use App\Enums\ExpenseCategory;
use App\Enums\ExpenseStatus;
use App\Enums\ExpenseType;
use App\Exceptions\ExpenseAlreadyProcessedException;
use App\Models\Expense;
use App\Services\ExpenseService;
use Tests\Concerns\SetsUpWashBusiness;
use Tests\TestCase;

class ExpenseServiceTest extends TestCase
{
    use SetsUpWashBusiness;

    private ExpenseService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpWashBusiness();
        $this->service = new ExpenseService;
    }

    public function test_it_submits_expense_as_pending(): void
    {
        $expense = $this->service->submit([
            'organization_id' => $this->organization->id,
            'site_id' => $this->site->id,
            'type' => ExpenseType::Variable->value,
            'category' => ExpenseCategory::Consumables->value,
            'description' => 'Car shampoo',
            'amount' => 1500,
            'date' => now()->toDateString(),
        ], $this->manager);

        $this->assertInstanceOf(Expense::class, $expense);
        $this->assertEquals(ExpenseStatus::Pending, $expense->status);
        $this->assertEquals($this->manager->id, $expense->submitted_by_id);
        $this->assertDatabaseHas('expenses', [
            'description' => 'Car shampoo',
            'amount' => 1500,
            'status' => 'pending',
        ]);
    }

    public function test_admin_can_approve_pending_expense(): void
    {
        $expense = $this->service->submit([
            'organization_id' => $this->organization->id,
            'site_id' => $this->site->id,
            'type' => ExpenseType::Fixed->value,
            'category' => ExpenseCategory::StaffHousing->value,
            'description' => 'Housing rent',
            'amount' => 50000,
            'date' => now()->toDateString(),
        ], $this->manager);

        $approved = $this->service->approve($expense, $this->admin);

        $this->assertEquals(ExpenseStatus::Approved, $approved->status);
        $this->assertEquals($this->admin->id, $approved->approved_by_id);
        $this->assertNotNull($approved->approved_at);
    }

    public function test_admin_can_reject_pending_expense(): void
    {
        $expense = $this->service->submit([
            'organization_id' => $this->organization->id,
            'site_id' => $this->site->id,
            'type' => ExpenseType::Variable->value,
            'category' => ExpenseCategory::Other->value,
            'description' => 'Misc',
            'amount' => 100,
            'date' => now()->toDateString(),
        ], $this->manager);

        $rejected = $this->service->reject($expense, $this->admin);

        $this->assertEquals(ExpenseStatus::Rejected, $rejected->status);
    }

    public function test_cannot_approve_already_approved_expense(): void
    {
        $expense = $this->service->submit([
            'organization_id' => $this->organization->id,
            'site_id' => $this->site->id,
            'type' => ExpenseType::Variable->value,
            'category' => ExpenseCategory::Consumables->value,
            'description' => 'Towels',
            'amount' => 500,
            'date' => now()->toDateString(),
        ], $this->manager);

        $this->service->approve($expense, $this->admin);

        $this->expectException(ExpenseAlreadyProcessedException::class);
        $this->service->approve($expense->fresh(), $this->admin);
    }

    public function test_approved_expenses_for_site_in_month(): void
    {
        $this->service->approve(
            $this->service->submit([
                'organization_id' => $this->organization->id,
                'site_id' => $this->site->id,
                'type' => ExpenseType::Variable->value,
                'category' => ExpenseCategory::Consumables->value,
                'description' => 'Chemicals',
                'amount' => 2000,
                'date' => now()->toDateString(),
            ], $this->manager),
            $this->admin
        );

        $this->service->submit([
            'organization_id' => $this->organization->id,
            'site_id' => $this->site->id,
            'type' => ExpenseType::Variable->value,
            'category' => ExpenseCategory::Consumables->value,
            'description' => 'Pending only',
            'amount' => 9999,
            'date' => now()->toDateString(),
        ], $this->manager);

        $total = $this->service->approvedTotalForSite(
            $this->site->id,
            now()->year,
            now()->month
        );

        $this->assertEquals(2000.0, $total);
    }
}
