<?php

namespace App\Services;

use App\Models\Assignment;
use App\Models\Employee;
use App\Models\Expense;
use App\Models\Fine;
use App\Models\Loan;
use App\Models\Maintenance;
use App\Models\Motorbike;
use App\Models\Payroll;
use App\Models\PlatformIncome;
use App\Models\User;

class DashboardService
{
    public function getOwnerDashboard(): array
    {
        return [
            'operational' => $this->getOperationalKPIs(),
            'financial'   => $this->getFinancialKPIs(),
            'alerts'      => $this->getAllAlerts(),
        ];
    }

    public function getAdminDashboard(): array
    {
        return [
            'operational' => $this->getOperationalKPIs(),
            'alerts'      => $this->getOperationalAlerts(),
        ];
    }

    private function getOperationalKPIs(): array
    {
        $empBase  = fn() => Employee::query();
        $bikeBase = fn() => Motorbike::query();

        return [
            'employees' => [
                'total'    => $empBase()->count(),
                'active'   => $empBase()->where('status', 'active')->count(),
                'inactive' => $empBase()->where('status', 'inactive')->count(),
            ],
            'motorbikes' => [
                'total'     => $bikeBase()->count(),
                'available' => $bikeBase()->where('status', 'available')->count(),
                'assigned'  => $bikeBase()->where('status', 'assigned')->count(),
                'inactive'  => $bikeBase()->where('status', 'inactive')->count(),
            ],
            'assignments' => [
                'active'   => Assignment::where('status', 'active')->count(),
                'returned_this_month' => Assignment::where('status', 'returned')
                    ->whereMonth('updated_at', now()->month)
                    ->whereYear('updated_at', now()->year)
                    ->count(),
            ],
            'loans' => [
                'active'      => Loan::where('status', 'active')->count(),
                'outstanding' => Loan::where('status', 'active')->sum('remaining_balance'),
            ],
            'fines' => [
                'pending'        => Fine::where('status', 'pending')->count(),
                'pending_amount' => Fine::where('status', 'pending')->sum('amount'),
            ],
            'maintenance' => [
                'upcoming_due' => Maintenance::whereNotNull('next_maintenance_date')
                    ->whereDate('next_maintenance_date', '<=', now()->addDays(30))
                    ->whereDate('next_maintenance_date', '>=', now())
                    ->count(),
                'in_progress' => Maintenance::where('status', 'in_progress')->count(),
            ],
            'payroll' => [
                'current_month_draft'    => Payroll::where('month', now()->month)->where('year', now()->year)->where('payroll_status', 'draft')->count(),
                'current_month_approved' => Payroll::where('month', now()->month)->where('year', now()->year)->where('payroll_status', 'approved')->count(),
                'current_month_unpaid'   => Payroll::where('month', now()->month)->where('year', now()->year)->where('payment_status', 'unpaid')->where('payroll_status', 'approved')->count(),
            ],
        ];
    }

    private function getFinancialKPIs(): array
    {
        $month = now()->month;
        $year  = now()->year;

        $monthlyIncome = PlatformIncome::whereMonth('income_date', $month)
            ->whereYear('income_date', $year)
            ->sum('amount');

        $monthlyPayroll = Payroll::where('month', $month)->where('year', $year)
            ->where('payroll_status', 'approved')
            ->sum('net_salary');

        $monthlyExpenses = Expense::whereMonth('expense_date', $month)
            ->whereYear('expense_date', $year)
            ->where('status', 'approved')
            ->sum('amount');

        $monthlyMaintenance = Maintenance::whereMonth('maintenance_date', $month)
            ->whereYear('maintenance_date', $year)
            ->where('status', 'completed')
            ->sum('cost');

        $totalExpenses = $monthlyPayroll + $monthlyExpenses + $monthlyMaintenance;
        $netProfit     = $monthlyIncome - $totalExpenses;

        return [
            'current_month' => [
                'month'       => $month,
                'year'        => $year,
                'income'      => (float) $monthlyIncome,
                'payroll'     => (float) $monthlyPayroll,
                'expenses'    => (float) $monthlyExpenses,
                'maintenance' => (float) $monthlyMaintenance,
                'net_profit'  => (float) $netProfit,
            ],
            'totals' => [
                'total_income_ever'    => (float) PlatformIncome::sum('amount'),
                'total_loans_active'   => (float) Loan::where('status', 'active')->sum('remaining_balance'),
                'total_expenses_ever'  => (float) Expense::where('status', 'approved')->sum('amount'),
            ],
        ];
    }

    private function getOperationalAlerts(): array
    {
        // Employee document expiry
        $empAlerts = Employee::whereNotNull('passport_expiry')
            ->orWhereNotNull('emirates_id_expiry')
            ->orWhereNotNull('visa_expiry')
            ->orWhereNotNull('labour_card_expiry')
            ->orWhereNotNull('driving_license_expiry')
            ->get()
            ->flatMap(function ($emp) {
                $alerts = [];
                $docs = [
                    'passport'      => $emp->passport_expiry,
                    'emirates_id'   => $emp->emirates_id_expiry,
                    'visa'          => $emp->visa_expiry,
                    'labour_card'   => $emp->labour_card_expiry,
                    'driving_license' => $emp->driving_license_expiry,
                ];
                foreach ($docs as $docType => $expiry) {
                    if (!$expiry) continue;
                    $daysLeft = now()->diffInDays($expiry, false);
                    if ($daysLeft <= 30) {
                        $alerts[] = [
                            'type'       => 'employee_document',
                            'severity'   => $daysLeft < 0 ? 'expired' : ($daysLeft <= 15 ? 'critical' : 'warning'),
                            'entity_id'  => $emp->id,
                            'entity_ref' => $emp->employee_id,
                            'entity_name'=> $emp->name,
                            'document'   => $docType,
                            'expiry_date'=> $expiry,
                            'days_left'  => (int) $daysLeft,
                        ];
                    }
                }
                return $alerts;
            })
            ->values();

        // Motorbike document expiry
        $bikeAlerts = Motorbike::whereNotNull('insurance_expiry')
            ->orWhereNotNull('mulkiya_expiry')
            ->get()
            ->flatMap(function ($bike) {
                $alerts = [];
                $docs = [
                    'insurance' => $bike->insurance_expiry,
                    'mulkiya'   => $bike->mulkiya_expiry,
                ];
                foreach ($docs as $docType => $expiry) {
                    if (!$expiry) continue;
                    $daysLeft = now()->diffInDays($expiry, false);
                    if ($daysLeft <= 30) {
                        $alerts[] = [
                            'type'       => 'motorbike_document',
                            'severity'   => $daysLeft < 0 ? 'expired' : ($daysLeft <= 15 ? 'critical' : 'warning'),
                            'entity_id'  => $bike->id,
                            'entity_ref' => $bike->bike_id,
                            'entity_name'=> $bike->plate_number,
                            'document'   => $docType,
                            'expiry_date'=> $expiry,
                            'days_left'  => (int) $daysLeft,
                        ];
                    }
                }
                return $alerts;
            })
            ->values();

        // Upcoming maintenance
        $maintenanceAlerts = Maintenance::with('motorbike:id,bike_id,plate_number')
            ->whereNotNull('next_maintenance_date')
            ->whereDate('next_maintenance_date', '<=', now()->addDays(14))
            ->whereDate('next_maintenance_date', '>=', now())
            ->get()
            ->map(fn($m) => [
                'type'       => 'maintenance_due',
                'severity'   => 'warning',
                'entity_id'  => $m->motorbike_id,
                'entity_ref' => $m->motorbike->bike_id ?? '',
                'entity_name'=> $m->motorbike->plate_number ?? '',
                'document'   => $m->maintenance_type,
                'expiry_date'=> $m->next_maintenance_date,
                'days_left'  => (int) now()->diffInDays($m->next_maintenance_date, false),
            ]);

        return [
            'employee_documents' => $empAlerts,
            'motorbike_documents' => $bikeAlerts,
            'maintenance_due'    => $maintenanceAlerts,
            'total_alerts'       => $empAlerts->count() + $bikeAlerts->count() + $maintenanceAlerts->count(),
        ];
    }

    private function getAllAlerts(): array
    {
        $operational = $this->getOperationalAlerts();

        // Financial alerts
        $pendingExpenses = Expense::where('status', 'pending')->count();
        $pendingFines    = Fine::where('status', 'pending')->sum('amount');
        $unpaidPayrolls  = Payroll::where('payroll_status', 'approved')
            ->where('payment_status', 'unpaid')
            ->count();

        $operational['financial_alerts'] = [
            'pending_expenses'   => $pendingExpenses,
            'pending_fines_amount' => (float) $pendingFines,
            'unpaid_payrolls'    => $unpaidPayrolls,
        ];

        return $operational;
    }
}
