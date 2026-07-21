<?php

namespace Tests\Feature;

use App\Enums\ExpenseCategory;
use App\Enums\ExpenseStatus;
use App\Enums\ExpenseType;
use App\Filament\Pages\SitePnLReport;
use App\Filament\Resources\ExpenseResource\Pages\CreateExpense;
use App\Filament\Resources\ExpenseResource\Pages\ListExpenses;
use App\Models\Expense;
use App\Services\ExpenseService;
use Livewire\Livewire;
use Tests\Concerns\SetsUpWashBusiness;
use Tests\TestCase;

class ExpenseResourceTest extends TestCase
{
    use SetsUpWashBusiness;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpWashBusiness();
    }

    public function test_manager_can_create_expense(): void
    {
        Livewire::actingAs($this->manager)
            ->test(CreateExpense::class)
            ->fillForm([
                'site_id' => $this->site->id,
                'type' => ExpenseType::Variable->value,
                'category' => ExpenseCategory::Consumables->value,
                'description' => 'Shampoo',
                'amount' => 1200,
                'date' => now()->toDateString(),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('expenses', [
            'description' => 'Shampoo',
            'status' => ExpenseStatus::Pending->value,
            'submitted_by_id' => $this->manager->id,
        ]);
    }

    public function test_admin_can_approve_expense_from_table(): void
    {
        $expense = app(ExpenseService::class)->submit([
            'organization_id' => $this->organization->id,
            'site_id' => $this->site->id,
            'type' => ExpenseType::Variable->value,
            'category' => ExpenseCategory::Consumables->value,
            'description' => 'Towels',
            'amount' => 800,
            'date' => now()->toDateString(),
        ], $this->manager);

        Livewire::actingAs($this->admin)
            ->test(ListExpenses::class)
            ->callTableAction('approve', $expense);

        $this->assertEquals(ExpenseStatus::Approved, $expense->fresh()->status);
    }

    public function test_site_pnl_page_loads_for_admin(): void
    {
        Livewire::actingAs($this->admin)
            ->test(SitePnLReport::class)
            ->assertOk();
    }
}
