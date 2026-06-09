<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\Fine;
use App\Models\Loan;
use App\Models\Payroll;
use App\Models\PlatformIncome;
use Illuminate\Support\Facades\DB;

class ProfitLossService
{
    public function getSummary(array $filters = []): array
    {
        $from = $filters['from_date'] ?? null;
        $to   = $filters['to_date'] ?? null;
        $month = $filters['month'] ?? null;
        $year  = $filters['year'] ?? null;

        // ── INCOME ────────────────────────────────────────────────────────────
        $incomeQuery = fn() => PlatformIncome::query()
            ->when($from, fn($q, $v) => $q->whereDate('income_date', '>=', $v))
            ->when($to, fn($q, $v) => $q->whereDate('income_date', '<=', $v))
            ->when($month, fn($q, $v) => $q->whereMonth('income_date', $v))
            ->when($year, fn($q, $v) => $q->whereYear('income_date', $v));

        $platformIncome = $incomeQuery()->where('source_type', 'platform')->sum('amount');
        $riderIncome    = $incomeQuery()->where('source_type', 'rider')->sum('amount');
        $totalIncome    = $platformIncome + $riderIncome;

        // ── EXPENSES ──────────────────────────────────────────────────────────

        // 1. Payroll (net salary of paid payrolls)
        $payrollExpense = Payroll::query()
            ->where('payment_status', 'paid')
            ->when($month, fn($q, $v) => $q->where('month', $v))
            ->when($year, fn($q, $v) => $q->where('year', $v))
            ->when($from, fn($q) => $q->whereRaw("STR_TO_DATE(CONCAT(year,'-',LPAD(month,2,'0'),'-01'), '%Y-%m-%d') >= ?", [$from]))
            ->when($to, fn($q) => $q->whereRaw("STR_TO_DATE(CONCAT(year,'-',LPAD(month,2,'0'),'-01'), '%Y-%m-%d') <= ?", [$to]))
            ->sum('net_salary');

        // 2. Operational expenses (approved)
        $operationalExpense = Expense::query()
            ->where('status', 'approved')
            ->when($from, fn($q, $v) => $q->whereDate('expense_date', '>=', $v))
            ->when($to, fn($q, $v) => $q->whereDate('expense_date', '<=', $v))
            ->when($month, fn($q, $v) => $q->whereMonth('expense_date', $v))
            ->when($year, fn($q, $v) => $q->whereYear('expense_date', $v))
            ->sum('amount');

        // 3. Loans disbursed (new loans created in period)
        $loansDisbursed = Loan::query()
            ->when($from, fn($q, $v) => $q->whereDate('loan_date', '>=', $v))
            ->when($to, fn($q, $v) => $q->whereDate('loan_date', '<=', $v))
            ->when($month, fn($q, $v) => $q->whereMonth('loan_date', $v))
            ->when($year, fn($q, $v) => $q->whereYear('loan_date', $v))
            ->sum('loan_amount');

        // 4. External fines (traffic/company fines that company covers — deducted or pending)
        $externalFines = Fine::query()
            ->whereIn('fine_type', ['traffic_fine', 'company_penalty'])
            ->when($from, fn($q, $v) => $q->whereDate('fine_date', '>=', $v))
            ->when($to, fn($q, $v) => $q->whereDate('fine_date', '<=', $v))
            ->when($month, fn($q, $v) => $q->whereMonth('fine_date', $v))
            ->when($year, fn($q, $v) => $q->whereYear('fine_date', $v))
            ->sum('amount');

        $totalExpenses = $payrollExpense + $operationalExpense + $loansDisbursed + $externalFines;
        $netProfit     = $totalIncome - $totalExpenses;

        // ── EXPENSE BREAKDOWN BY CATEGORY ─────────────────────────────────────
        $expenseByCategory = Expense::query()
            ->where('status', 'approved')
            ->when($from, fn($q, $v) => $q->whereDate('expense_date', '>=', $v))
            ->when($to, fn($q, $v) => $q->whereDate('expense_date', '<=', $v))
            ->when($month, fn($q, $v) => $q->whereMonth('expense_date', $v))
            ->when($year, fn($q, $v) => $q->whereYear('expense_date', $v))
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->pluck('total', 'category');

        // ── INCOME BREAKDOWN BY PLATFORM ──────────────────────────────────────
        $incomeByPlatform = PlatformIncome::query()
            ->where('source_type', 'platform')
            ->when($from, fn($q, $v) => $q->whereDate('income_date', '>=', $v))
            ->when($to, fn($q, $v) => $q->whereDate('income_date', '<=', $v))
            ->when($month, fn($q, $v) => $q->whereMonth('income_date', $v))
            ->when($year, fn($q, $v) => $q->whereYear('income_date', $v))
            ->selectRaw('platform_name, SUM(amount) as total')
            ->groupBy('platform_name')
            ->pluck('total', 'platform_name');

        return [
            'period' => [
                'from_date' => $from,
                'to_date'   => $to,
                'month'     => $month,
                'year'      => $year,
            ],
            'income' => [
                'platform_income'    => (float) $platformIncome,
                'rider_income'       => (float) $riderIncome,
                'total_income'       => (float) $totalIncome,
                'by_platform'        => $incomeByPlatform,
            ],
            'expenses' => [
                'payroll'            => (float) $payrollExpense,
                'operational'        => (float) $operationalExpense,
                'loans_disbursed'    => (float) $loansDisbursed,
                'external_fines'     => (float) $externalFines,
                'total_expenses'     => (float) $totalExpenses,
                'by_category'        => $expenseByCategory,
            ],
            'summary' => [
                'total_income'    => (float) $totalIncome,
                'total_expenses'  => (float) $totalExpenses,
                'net_profit'      => (float) $netProfit,
                'profit_margin'   => $totalIncome > 0
                    ? round(($netProfit / $totalIncome) * 100, 2)
                    : 0,
                'is_profitable'   => $netProfit >= 0,
            ],
        ];
    }

    public function getMonthlyTrend(int $year): array
    {
        $months = [];

        for ($m = 1; $m <= 12; $m++) {
            $income = PlatformIncome::whereYear('income_date', $year)
                ->whereMonth('income_date', $m)
                ->sum('amount');

            $expenses = Payroll::where('year', $year)->where('month', $m)
                ->where('payment_status', 'paid')
                ->sum('net_salary')
                + Expense::whereYear('expense_date', $year)
                    ->whereMonth('expense_date', $m)
                    ->where('status', 'approved')
                    ->sum('amount');

            $months[] = [
                'month'      => $m,
                'month_name' => date('F', mktime(0, 0, 0, $m, 1)),
                'income'     => (float) $income,
                'expenses'   => (float) $expenses,
                'net_profit' => (float) ($income - $expenses),
            ];
        }

        return $months;
    }
}
