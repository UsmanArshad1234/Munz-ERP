<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Expense;
use App\Models\Fine;
use App\Models\Loan;
use App\Models\Motorbike;
use App\Models\Payroll;
use App\Models\PlatformIncome;
use App\Models\Setting;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    // ── Public API ────────────────────────────────────────────────────────────

    public function getOwnerDashboard(int $month, int $year, array $filters = []): array
    {
        $expiryAlerts = $this->getExpiryAlerts();

        return [
            'month'                => sprintf('%04d-%02d', $year, $month),
            'filters_applied'      => $this->describeFilters($filters),
            'employees'            => $this->getEmployeeStats($filters),
            'bikes'                => $this->getBikeStats($filters),
            'emirate_breakdown'    => $this->getEmirateBreakdown($filters),
            'salary_summary'       => $this->getSalarySummary($month, $year, $filters),
            'loans_fines_salik'    => $this->getLoansFinesSalik($month, $year, $filters),
            'platform_income'      => $this->getPlatformIncome($month, $year, $filters),
            'expenses_by_category' => $this->getExpensesByCategory($month, $year),
            'net_profit'           => $this->getNetProfit($month, $year, $filters),
            'status_checks'        => $this->getStatusChecks($month, $year, $filters),
            'expiry_alerts'        => $expiryAlerts,
            'alerts'               => $this->getAllAlerts($expiryAlerts),
        ];
    }

    public function getAdminDashboard(int $month, int $year, array $filters = []): array
    {
        return [
            'month'             => sprintf('%04d-%02d', $year, $month),
            'filters_applied'   => $this->describeFilters($filters),
            'employees'         => $this->getEmployeeStats($filters),
            'bikes'             => $this->getBikeStats($filters),
            'emirate_breakdown' => $this->getEmirateBreakdown($filters),
            'expiry_alerts'     => $this->getExpiryAlerts(),
        ];
    }

    public function getStatusChecks(int $month, int $year, array $filters = []): array
    {
        $empIds = $this->getFilteredEmployeeIds($filters);

        $incomeQ = PlatformIncome::whereMonth('income_date', $month)->whereYear('income_date', $year);
        if ($v = $filters['platform'] ?? null) {
            $incomeQ->whereRaw('LOWER(platform_name) = ?', [strtolower($v)]);
        }

        $payrollQ = Payroll::where('month', $month)->where('year', $year);
        $fineQ    = Fine::whereMonth('fine_date', $month)->whereYear('fine_date', $year);
        $salikQ   = Fine::where('fine_type', 'salik')->whereMonth('fine_date', $month)->whereYear('fine_date', $year);

        if ($empIds !== null) {
            $payrollQ->whereIn('employee_id', $empIds);
            $fineQ->whereIn('employee_id', $empIds);
            $salikQ->whereIn('employee_id', $empIds);
        }

        return [
            'month'        => sprintf('%04d-%02d', $year, $month),
            'income_rows'  => $incomeQ->count(),
            'expense_rows' => Expense::whereMonth('expense_date', $month)->whereYear('expense_date', $year)->count(),
            'payroll_rows' => $payrollQ->count(),
            'fine_rows'    => (clone $fineQ)->where('fine_type', '!=', 'salik')->count(),
            'salik_rows'   => $salikQ->count(),
        ];
    }

    // ── Filter Helpers ────────────────────────────────────────────────────────

    private function applyEmpFilters(Builder $query, array $filters): Builder
    {
        if ($v = $filters['emirate']         ?? null) $query->where('work_emirate',  $v);
        if ($v = $filters['zone']            ?? null) $query->where('zone',           $v);
        if ($v = $filters['platform']        ?? null) $query->where('platform_name',  $v);
        if ($v = $filters['employee_status'] ?? null) $query->where('status',         $v);
        if ($v = $filters['employee_id']     ?? null) $query->where('id',             $v);
        return $query;
    }

    private function applyBikeFilters(Builder $query, array $filters): Builder
    {
        if ($v = $filters['emirate']     ?? null) $query->where('emirate',           $v);
        if ($v = $filters['zone']        ?? null) $query->where('zone',              $v);
        if ($v = $filters['bike_status'] ?? null) $query->where('status',            strtolower(str_replace(' ', '_', $v)));
        if ($v = $filters['employee_id'] ?? null) $query->where('current_rider_id',  $v);
        return $query;
    }

    /**
     * Returns employee IDs matching employee-related filters, or null if no
     * such filters are active (null = "no restriction, include all employees").
     */
    private function getFilteredEmployeeIds(array $filters): ?array
    {
        $hasFilter = !empty($filters['emirate'])
            || !empty($filters['zone'])
            || !empty($filters['platform'])
            || !empty($filters['employee_status'])
            || !empty($filters['employee_id']);

        if (!$hasFilter) return null;

        return $this->applyEmpFilters(Employee::query(), $filters)->pluck('id')->toArray();
    }

    private function describeFilters(array $filters): array
    {
        return array_filter($filters);
    }

    // ── Data Methods ──────────────────────────────────────────────────────────

    private function getEmployeeStats(array $filters): array
    {
        $base = fn() => $this->applyEmpFilters(Employee::query(), $filters);

        $total    = $base()->count();
        $active   = $base()->where('status', 'active')->count();
        $inactive = $base()->where('status', 'inactive')->count();

        $platformCounts = $base()
            ->where('status', 'active')
            ->whereNotNull('platform_name')
            ->select(DB::raw('LOWER(platform_name) as platform, COUNT(*) as total'))
            ->groupBy(DB::raw('LOWER(platform_name)'))
            ->pluck('total', 'platform')
            ->toArray();

        return [
            'total_employees'    => $total,
            'active_employees'   => $active,
            'inactive_employees' => $inactive,
            'noon_riders'        => (int) ($platformCounts['noon']    ?? 0),
            'talabat_riders'     => (int) ($platformCounts['talabat'] ?? 0),
            'keeta_riders'       => (int) ($platformCounts['keeta']   ?? 0),
            'careem_riders'      => (int) ($platformCounts['careem']  ?? 0),
            'other_riders'       => $this->countOtherRiders($platformCounts),
        ];
    }

    private function countOtherRiders(array $platformCounts): int
    {
        $known = ['noon', 'talabat', 'keeta', 'careem'];
        return (int) collect($platformCounts)
            ->filter(fn($count, $platform) => !in_array($platform, $known))
            ->sum();
    }

    private function getBikeStats(array $filters): array
    {
        $base = fn() => $this->applyBikeFilters(Motorbike::query(), $filters);

        $offRoadStatuses = ['inactive', 'sold', 'cancelled', 'damaged'];

        return [
            'total_bikes'          => $base()->count(),
            'assigned_bikes'       => $base()->where('status', 'assigned')->count(),
            'available_bikes'      => $base()->where('status', 'available')->count(),
            'maintenance_bikes'    => $base()->where('status', 'under_maintenance')->count(),
            'inactive_bikes'       => $base()->whereIn('status', $offRoadStatuses)->count(),
            'bikes_without_worker' => $base()->whereNull('current_rider_id')
                                             ->whereNotIn('status', $offRoadStatuses)->count(),
        ];
    }

    private function getEmirateBreakdown(array $filters): array
    {
        // Employee counts per emirate — apply zone/platform/employee_status but NOT emirate
        // (we are breaking DOWN by emirate; applying emirate filter restricts to one row)
        $empFiltersNoEmirate = array_diff_key($filters, ['emirate' => '']);
        $empQ = $this->applyEmpFilters(Employee::whereNotNull('work_emirate'), $empFiltersNoEmirate)
            ->select('work_emirate as emirate', DB::raw('COUNT(*) as total'))
            ->groupBy('work_emirate');

        if (!empty($filters['emirate'])) {
            $empQ->where('work_emirate', $filters['emirate']);
        }
        $empCounts = $empQ->pluck('total', 'emirate');

        // Bike counts per emirate — apply zone/bike_status but NOT emirate
        $bikeFiltersNoEmirate = array_diff_key($filters, ['emirate' => '']);
        $bikeQ = $this->applyBikeFilters(Motorbike::whereNotNull('emirate'), $bikeFiltersNoEmirate)
            ->select('emirate', DB::raw('COUNT(*) as total'))
            ->groupBy('emirate');

        if (!empty($filters['emirate'])) {
            $bikeQ->where('emirate', $filters['emirate']);
        }
        $bikeCounts = $bikeQ->pluck('total', 'emirate');

        // Known emirates from settings (respects emirate filter if set)
        $settingQ = Setting::active()->ofType('work_emirate')->orderBy('sort_order');
        if (!empty($filters['emirate'])) {
            $settingQ->where('value', $filters['emirate']);
        }
        $settingEmirates = $settingQ->pluck('value');

        $allEmirates = $settingEmirates
            ->merge($empCounts->keys())
            ->merge($bikeCounts->keys())
            ->unique()
            ->values();

        return $allEmirates->map(fn($emirate) => [
            'emirate'   => $emirate,
            'employees' => (int) ($empCounts[$emirate]  ?? 0),
            'bikes'     => (int) ($bikeCounts[$emirate] ?? 0),
        ])->toArray();
    }

    private function getSalarySummary(int $month, int $year, array $filters): array
    {
        $empIds = $this->getFilteredEmployeeIds($filters);

        $q = function () use ($month, $year, $empIds) {
            $query = Payroll::where('month', $month)->where('year', $year);
            if ($empIds !== null) $query->whereIn('employee_id', $empIds);
            return $query;
        };

        return [
            'gross_payroll'   => (float) $q()->sum('gross_salary'),
            'loans_deduction' => (float) $q()->sum('loan_deduction'),
            'fines_deduction' => (float) $q()->sum('fine_deduction'),
            'salik_deduction' => (float) $q()->sum('salik_deduction'),
            'net_payroll'     => (float) $q()->sum('net_salary'),
            'payroll_rows'    => $q()->count(),
        ];
    }

    private function getLoansFinesSalik(int $month, int $year, array $filters): array
    {
        $empIds = $this->getFilteredEmployeeIds($filters);

        $loanQ = Loan::where('status', 'active');
        $fineQ = Fine::whereMonth('fine_date', $month)->whereYear('fine_date', $year)
                     ->where('fine_type', '!=', 'salik');
        $salikQ    = Payroll::where('month', $month)->where('year', $year);
        $pendingQ  = Fine::where('status', 'pending');

        if ($empIds !== null) {
            $loanQ->whereIn('employee_id', $empIds);
            $fineQ->whereIn('employee_id', $empIds);
            $salikQ->whereIn('employee_id', $empIds);
            $pendingQ->whereIn('employee_id', $empIds);
        }

        return [
            'active_loans_remaining' => (float) $loanQ->sum('remaining_balance'),
            'fines_this_month'       => (float) $fineQ->sum('amount'),
            'salik_this_month'       => (float) $salikQ->sum('salik_deduction'),
            'pending_deductions'     => (float) $pendingQ->sum('amount'),
        ];
    }

    private function getPlatformIncome(int $month, int $year, array $filters): array
    {
        $base = PlatformIncome::whereMonth('income_date', $month)
            ->whereYear('income_date', $year)
            ->whereNotNull('platform_name');

        // Apply platform filter if set
        if ($v = $filters['platform'] ?? null) {
            $base->whereRaw('LOWER(platform_name) = ?', [strtolower($v)]);
        }

        $grouped = (clone $base)
            ->select(DB::raw('LOWER(platform_name) as platform'), DB::raw('SUM(amount) as total'))
            ->groupBy(DB::raw('LOWER(platform_name)'))
            ->pluck('total', 'platform');

        $known = ['noon', 'talabat', 'careem', 'keeta'];
        $other = $grouped->filter(fn($amt, $p) => !in_array($p, $known))->sum();
        $total = (float) (clone $base)->sum('amount');

        return [
            'noon'    => (float) ($grouped['noon']    ?? 0),
            'talabat' => (float) ($grouped['talabat'] ?? 0),
            'careem'  => (float) ($grouped['careem']  ?? 0),
            'keeta'   => (float) ($grouped['keeta']   ?? 0),
            'other'   => (float) $other,
            'total'   => $total,
        ];
    }

    private function getExpensesByCategory(int $month, int $year): array
    {
        $grouped = Expense::where('status', 'approved')
            ->whereMonth('expense_date', $month)
            ->whereYear('expense_date', $year)
            ->select('category', DB::raw('SUM(amount) as total'))
            ->groupBy('category')
            ->pluck('total', 'category');

        $categories = Setting::active()
            ->ofType('expense_category')
            ->orderBy('sort_order')
            ->pluck('value');

        $allCategories = $categories->merge($grouped->keys())->unique()->values();

        $breakdown = $allCategories->map(fn($cat) => [
            'category' => $cat,
            'amount'   => (float) ($grouped[$cat] ?? 0),
        ])->toArray();

        $total = (float) Expense::where('status', 'approved')
            ->whereMonth('expense_date', $month)
            ->whereYear('expense_date', $year)
            ->sum('amount');

        return [
            'breakdown' => $breakdown,
            'total'     => $total,
        ];
    }

    private function getNetProfit(int $month, int $year, array $filters): array
    {
        $empIds = $this->getFilteredEmployeeIds($filters);

        // Platform income (filtered by platform if set)
        $incomeQ = PlatformIncome::whereMonth('income_date', $month)->whereYear('income_date', $year);
        if ($v = $filters['platform'] ?? null) {
            $incomeQ->whereRaw('LOWER(platform_name) = ?', [strtolower($v)]);
        }
        $income = (float) $incomeQ->sum('amount');

        // Approved payroll (filtered by employee if applicable)
        $payrollQ = Payroll::where('month', $month)->where('year', $year)
            ->where('payroll_status', 'approved');
        if ($empIds !== null) $payrollQ->whereIn('employee_id', $empIds);
        $payroll = (float) $payrollQ->sum('net_salary');

        // Expenses — not filtered by employee (company-wide)
        $expenses = (float) Expense::where('status', 'approved')
            ->whereMonth('expense_date', $month)
            ->whereYear('expense_date', $year)
            ->sum('amount');

        // Company penalty fines — not filtered by employee
        $companyFines = (float) Fine::where('fine_type', 'company_penalty')
            ->whereMonth('fine_date', $month)
            ->whereYear('fine_date', $year)
            ->sum('amount');

        // Salik from payroll (filtered by employee if applicable)
        $salikQ = Payroll::where('month', $month)->where('year', $year);
        if ($empIds !== null) $salikQ->whereIn('employee_id', $empIds);
        $companySalik = (float) $salikQ->sum('salik_deduction');

        return [
            'platform_income' => $income,
            'payroll'         => $payroll,
            'expenses'        => $expenses,
            'company_fines'   => $companyFines,
            'company_salik'   => $companySalik,
            'muzn_net_profit' => round($income - $payroll - $expenses - $companyFines - $companySalik, 2),
        ];
    }

    private function getExpiryAlerts(): array
    {
        $empAlerts = Employee::where(function ($q) {
                $q->whereNotNull('passport_expiry')
                  ->orWhereNotNull('emirates_id_expiry')
                  ->orWhereNotNull('visa_expiry')
                  ->orWhereNotNull('labour_card_expiry')
                  ->orWhereNotNull('driving_license_expiry');
            })
            ->get(['id', 'employee_id', 'name', 'passport_expiry', 'emirates_id_expiry',
                   'visa_expiry', 'labour_card_expiry', 'driving_license_expiry'])
            ->flatMap(function ($emp) {
                $docs = [
                    'Passport'        => $emp->passport_expiry,
                    'Emirates ID'     => $emp->emirates_id_expiry,
                    'Visa'            => $emp->visa_expiry,
                    'Labour Card'     => $emp->labour_card_expiry,
                    'Driving License' => $emp->driving_license_expiry,
                ];
                $alerts = [];
                foreach ($docs as $label => $expiry) {
                    if (!$expiry) continue;
                    $daysLeft = (int) now()->startOfDay()->diffInDays($expiry, false);
                    if ($daysLeft <= 60) {
                        $alerts[] = [
                            'entity_type' => 'employee',
                            'entity_id'   => $emp->id,
                            'entity_ref'  => $emp->employee_id,
                            'entity_name' => $emp->name,
                            'document'    => $label,
                            'expiry_date' => $expiry,
                            'days_left'   => $daysLeft,
                            'severity'    => $daysLeft < 0 ? 'expired' : ($daysLeft <= 15 ? 'critical' : 'warning'),
                            'message'     => $daysLeft < 0
                                ? "{$emp->name} - {$label} - Expired"
                                : "{$emp->name} - {$label} - Expiring in {$daysLeft} days",
                        ];
                    }
                }
                return $alerts;
            })
            ->sortBy('days_left')
            ->values();

        $bikeAlerts = Motorbike::where(function ($q) {
                $q->whereNotNull('insurance_expiry')
                  ->orWhereNotNull('mulkiya_expiry');
            })
            ->get(['id', 'bike_id', 'plate_number', 'insurance_expiry', 'mulkiya_expiry'])
            ->flatMap(function ($bike) {
                $docs = [
                    'Insurance' => $bike->insurance_expiry,
                    'Mulkiya'   => $bike->mulkiya_expiry,
                ];
                $alerts = [];
                foreach ($docs as $label => $expiry) {
                    if (!$expiry) continue;
                    $daysLeft = (int) now()->startOfDay()->diffInDays($expiry, false);
                    if ($daysLeft <= 60) {
                        $alerts[] = [
                            'entity_type' => 'motorbike',
                            'entity_id'   => $bike->id,
                            'entity_ref'  => $bike->bike_id,
                            'entity_name' => $bike->plate_number,
                            'document'    => $label,
                            'expiry_date' => $expiry,
                            'days_left'   => $daysLeft,
                            'severity'    => $daysLeft < 0 ? 'expired' : ($daysLeft <= 15 ? 'critical' : 'warning'),
                            'message'     => $daysLeft < 0
                                ? "Bike {$bike->plate_number} - {$label} - Expired"
                                : "Bike {$bike->plate_number} - {$label} - Expiring in {$daysLeft} days",
                        ];
                    }
                }
                return $alerts;
            })
            ->sortBy('days_left')
            ->values();

        $expired  = $empAlerts->where('severity', 'expired')->count()  + $bikeAlerts->where('severity', 'expired')->count();
        $critical = $empAlerts->where('severity', 'critical')->count() + $bikeAlerts->where('severity', 'critical')->count();
        $warning  = $empAlerts->where('severity', 'warning')->count()  + $bikeAlerts->where('severity', 'warning')->count();

        return [
            'employee_documents'  => $empAlerts,
            'motorbike_documents' => $bikeAlerts,
            'total_alerts'        => $empAlerts->count() + $bikeAlerts->count(),
            'expired_count'       => $expired,
            'critical_count'      => $critical,
            'warning_count'       => $warning,
        ];
    }

    private function getAllAlerts(array $expiryAlerts): array
    {
        $expiryAlerts['financial_alerts'] = [
            'pending_expenses'     => Expense::where('status', 'pending')->count(),
            'pending_fines_amount' => (float) Fine::where('status', 'pending')->sum('amount'),
            'unpaid_payrolls'      => Payroll::where('payroll_status', 'approved')
                ->where('payment_status', 'unpaid')->count(),
        ];

        return $expiryAlerts;
    }
}
