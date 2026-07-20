<?php

namespace Database\Seeders;

use App\Enums\ExpenseCategory;
use App\Enums\ExpenseType;
use App\Enums\PaymentMethod;
use App\Enums\Shift;
use App\Enums\UserRole;
use App\Models\Contract;
use App\Models\DailyLog;
use App\Models\Equipment;
use App\Models\Expense;
use App\Models\Organization;
use App\Models\Partner;
use App\Models\PartnerSiteShare;
use App\Models\ServiceType;
use App\Models\Site;
use App\Models\Staff;
use App\Models\StaffAssignment;
use App\Models\User;
use App\Models\WashEntry;
use App\Services\ExpenseService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $org = Organization::create(['name' => 'Premium Car Wash Co.']);

        $admin = User::create([
            'organization_id' => $org->id,
            'name' => 'Admin Owner',
            'email' => 'admin@carwash.test',
            'password' => Hash::make('password'),
            'role' => UserRole::Admin,
            'is_active' => true,
        ]);

        User::create([
            'organization_id' => $org->id,
            'name' => 'Accountant',
            'email' => 'accountant@carwash.test',
            'password' => Hash::make('password'),
            'role' => UserRole::Accountant,
            'is_active' => true,
        ]);

        $managers = collect([
            ['name' => 'Karim Ahmed', 'email' => 'karim@carwash.test'],
            ['name' => 'Rahim Uddin', 'email' => 'rahim@carwash.test'],
            ['name' => 'Hasan Ali', 'email' => 'hasan@carwash.test'],
            ['name' => 'Faruk Chowdhury', 'email' => 'faruk@carwash.test'],
        ])->map(fn (array $data) => User::create([
            'organization_id' => $org->id,
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make('password'),
            'role' => UserRole::SiteManager,
            'is_active' => true,
        ]));

        $partnerUsers = collect([
            ['name' => 'Partner One', 'email' => 'partner1@carwash.test'],
            ['name' => 'Partner Two', 'email' => 'partner2@carwash.test'],
        ])->map(fn (array $data) => User::create([
            'organization_id' => $org->id,
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make('password'),
            'role' => UserRole::Partner,
            'is_active' => true,
        ]));

        $sitesData = [
            [
                'name' => 'City Center Mall',
                'mall_name' => 'City Center Mall',
                'city' => 'Dhaka',
                'address' => 'Motijheel, Dhaka',
                'contract' => 600000,
                'price' => 200,
                'manager' => $managers[0],
                'contract_end' => '2025-12-31',
                'status' => 'active',
            ],
            [
                'name' => 'Bashundhara Shopping Complex',
                'mall_name' => 'Bashundhara Shopping Complex',
                'city' => 'Dhaka',
                'address' => 'Panthapath, Dhaka',
                'contract' => 480000,
                'price' => 180,
                'manager' => $managers[1],
                'contract_end' => '2026-02-28',
                'status' => 'active',
            ],
            [
                'name' => 'Jamuna Future Park',
                'mall_name' => 'Jamuna Future Park',
                'city' => 'Dhaka',
                'address' => 'Kuril, Dhaka',
                'contract' => 720000,
                'price' => 220,
                'manager' => $managers[2],
                'contract_end' => '2025-05-31',
                'status' => 'pending_renewal',
            ],
            [
                'name' => 'Chittagong GEC Circle Mall',
                'mall_name' => 'Chittagong GEC Circle Mall',
                'city' => 'Chittagong',
                'address' => 'GEC Circle, Chittagong',
                'contract' => 360000,
                'price' => 150,
                'manager' => $managers[3],
                'contract_end' => '2025-12-31',
                'status' => 'active',
            ],
        ];

        $staffBySite = [
            ['Jamal', 'Selim', 'Babul', 'Anwar', 'Tarek'],
            ['Mokbul', 'Nurul', 'Shahid', 'Kamal', 'Iqbal'],
            ['Rashid', 'Sohel', 'Mintu', 'Alamgir', 'Babu'],
            ['Nazmul', 'Rubel', 'Shakil', 'Imran', 'Dipu'],
        ];

        $sites = collect();
        $serviceTypes = collect();

        foreach ($sitesData as $index => $data) {
            $site = Site::create([
                'organization_id' => $org->id,
                'manager_id' => $data['manager']->id,
                'name' => $data['name'],
                'mall_name' => $data['mall_name'],
                'city' => $data['city'],
                'address' => $data['address'],
                'capacity' => 80,
                'is_active' => true,
            ]);

            Contract::create([
                'site_id' => $site->id,
                'title' => $data['mall_name'].' Annual Lease',
                'annual_value' => $data['contract'],
                'start_date' => Carbon::parse($data['contract_end'])->subYear()->addDay(),
                'end_date' => $data['contract_end'],
                'status' => $data['status'],
            ]);

            $serviceType = ServiceType::create([
                'site_id' => $site->id,
                'name' => 'Standard Wash',
                'price' => $data['price'],
                'is_active' => true,
            ]);

            $sites->push($site);
            $serviceTypes->push($serviceType);

            foreach ($staffBySite[$index] as $staffIndex => $staffName) {
                $staffType = $staffIndex === 4 ? 'supervisor' : 'washer';
                $salaryTypes = ['daily', 'daily', 'daily', 'monthly', 'per_car'];
                $salaryType = $salaryTypes[$staffIndex % 5];

                $staff = Staff::create([
                    'organization_id' => $org->id,
                    'employee_code' => 'EMP-'.str_pad((string) ($index * 5 + $staffIndex + 1), 3, '0', STR_PAD_LEFT),
                    'name' => $staffName,
                    'staff_type' => $staffType,
                    'salary_type' => $salaryType,
                    'base_salary' => $salaryType === 'monthly' ? 12000 : ($salaryType === 'daily' ? 500 : null),
                    'per_wash_rate' => $salaryType === 'per_car' ? 15 : null,
                    'has_housing' => true,
                    'daily_food_allowance' => 100,
                    'hire_date' => now()->subMonths(6),
                    'is_active' => true,
                ]);

                StaffAssignment::create([
                    'staff_id' => $staff->id,
                    'site_id' => $site->id,
                    'is_primary' => true,
                    'start_date' => now()->subMonths(6)->toDateString(),
                ]);
            }
        }

        $partner1 = Partner::create([
            'organization_id' => $org->id,
            'user_id' => $partnerUsers[0]->id,
            'name' => 'Abdul (Partner One)',
            'email' => 'partner1@carwash.test',
        ]);

        $partner2 = Partner::create([
            'organization_id' => $org->id,
            'user_id' => $partnerUsers[1]->id,
            'name' => 'Mohsin (Partner Two)',
            'email' => 'partner2@carwash.test',
        ]);

        PartnerSiteShare::create(['partner_id' => $partner1->id, 'site_id' => $sites[0]->id, 'share_pct' => 30]);
        PartnerSiteShare::create(['partner_id' => $partner1->id, 'site_id' => $sites[1]->id, 'share_pct' => 25]);
        PartnerSiteShare::create(['partner_id' => $partner2->id, 'site_id' => $sites[2]->id, 'share_pct' => 40]);
        PartnerSiteShare::create(['partner_id' => $partner2->id, 'site_id' => $sites[3]->id, 'share_pct' => 35]);

        for ($i = 5; $i >= 1; $i--) {
            User::create([
                'organization_id' => $org->id,
                'name' => 'Staff Demo '.$i,
                'email' => 'staff'.$i.'@carwash.test',
                'password' => Hash::make('password'),
                'role' => UserRole::Staff,
                'is_active' => true,
            ]);
        }

        $paymentMethods = [PaymentMethod::Cash, PaymentMethod::Cash, PaymentMethod::Cash, PaymentMethod::Upi, PaymentMethod::Card];

        foreach (range(1, 30) as $daysAgo) {
            $date = now()->subDays($daysAgo)->toDateString();
            $isWeekend = in_array(Carbon::parse($date)->dayOfWeek, [Carbon::SATURDAY, Carbon::SUNDAY], true);
            $multiplier = $isWeekend ? 1.2 : 1.0;

            foreach ($sites as $siteIndex => $site) {
                $siteStaff = Staff::whereHas('assignments', fn ($q) => $q->where('site_id', $site->id))->get();
                $serviceType = $serviceTypes[$siteIndex];

                foreach ([Shift::Morning, Shift::Evening] as $shift) {
                    $baseCars = $shift === Shift::Morning ? rand(20, 30) : rand(15, 25);
                    $totalCars = (int) round($baseCars * $multiplier);

                    $dailyLog = DailyLog::create([
                        'site_id' => $site->id,
                        'date' => $date,
                        'shift' => $shift,
                        'submitted_by_id' => $sitesData[$siteIndex]['manager']->id,
                        'is_closed' => true,
                    ]);

                    $remaining = $totalCars;
                    $shuffledStaff = $siteStaff->shuffle()->values();

                    foreach ($shuffledStaff as $position => $staffMember) {
                        if ($remaining <= 0) {
                            break;
                        }

                        $count = $position === $shuffledStaff->count() - 1
                            ? $remaining
                            : min($remaining, rand(2, max(2, (int) ceil($totalCars / 4))));

                        WashEntry::create([
                            'daily_log_id' => $dailyLog->id,
                            'staff_id' => $staffMember->id,
                            'service_type_id' => $serviceType->id,
                            'vehicle_count' => $count,
                            'payment_method' => $paymentMethods[array_rand($paymentMethods)],
                        ]);

                        $remaining -= $count;
                    }
                }
            }
        }

        $expenseService = new ExpenseService;

        foreach ($sites as $site) {
            Equipment::create([
                'site_id' => $site->id,
                'name' => 'Pressure Washer',
                'purchase_date' => now()->subMonths(8),
                'purchase_cost' => 45000,
                'status' => 'active',
            ]);

            Equipment::create([
                'site_id' => $site->id,
                'name' => 'Vacuum Cleaner',
                'purchase_date' => now()->subMonths(6),
                'purchase_cost' => 18000,
                'status' => 'active',
            ]);

            $expense = $expenseService->submit([
                'organization_id' => $org->id,
                'site_id' => $site->id,
                'type' => ExpenseType::Variable->value,
                'category' => ExpenseCategory::Consumables->value,
                'description' => 'Monthly chemicals & towels',
                'amount' => rand(3000, 8000),
                'date' => now()->subDays(5)->toDateString(),
            ], $admin);

            $expenseService->approve($expense, $admin);

            $expenseService->submit([
                'organization_id' => $org->id,
                'site_id' => $site->id,
                'type' => ExpenseType::Variable->value,
                'category' => ExpenseCategory::Utilities->value,
                'description' => 'Water pump electricity',
                'amount' => rand(1500, 3500),
                'date' => now()->toDateString(),
            ], $sites->first()->manager ?? $admin);
        }

        $expenseService->approve(
            $expenseService->submit([
                'organization_id' => $org->id,
                'site_id' => null,
                'type' => ExpenseType::Fixed->value,
                'category' => ExpenseCategory::StaffHousing->value,
                'description' => 'Staff housing monthly rent',
                'amount' => 80000,
                'date' => now()->startOfMonth()->toDateString(),
            ], $admin),
            $admin
        );

        $payrollService = new \App\Services\PayrollService;
        $payoutService = new \App\Services\PartnerPayoutService(
            new \App\Services\PnLService($expenseService)
        );

        foreach ($sites as $site) {
            $payrollService->generateForSite(
                $site,
                now()->startOfMonth(),
                now()->endOfMonth()
            );
        }

        foreach (Partner::all() as $partner) {
            $payoutService->createSettlement($partner, now()->year, now()->month);
        }
    }
}
