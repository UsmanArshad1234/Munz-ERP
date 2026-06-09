<?php

namespace App\Services;

use App\Models\Payroll;
use App\Models\Employee;
use App\Models\Loan;
use App\Models\LoanPayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use Barryvdh\DomPDF\Facade\Pdf;

class PayrollService
{
    public function generatePayrollId(): string
    {
        $last = Payroll::orderBy('id', 'desc')->lockForUpdate()->first();
        $next = $last ? ((int) substr($last->payroll_id, 4)) + 1 : 1;
        return 'PAY-' . str_pad($next, 4, '0', STR_PAD_LEFT);
    }

    public function list(array $filters): LengthAwarePaginator
    {
        $query = Payroll::with(['employee:id,employee_id,name', 'approver:id,name'])
            ->when($filters['employee_id'] ?? null, fn($q, $v) => $q->where('employee_id', $v))
            ->when($filters['month'] ?? null, fn($q, $v) => $q->where('month', $v))
            ->when($filters['year'] ?? null, fn($q, $v) => $q->where('year', $v))
            ->when($filters['payroll_status'] ?? null, fn($q, $v) => $q->where('payroll_status', $v))
            ->when($filters['payment_status'] ?? null, fn($q, $v) => $q->where('payment_status', $v))
            ->when($filters['search'] ?? null, function ($q, $search) {
                $q->where(function ($q) use ($search) {
                    $q->where('payroll_id', 'like', "%$search%")
                      ->orWhereHas('employee', fn($eq) => $eq->where('name', 'like', "%$search%")
                          ->orWhere('employee_id', 'like', "%$search%"));
                });
            })
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc');

        return $query->paginate($filters['per_page'] ?? 15);
    }

    public function create(array $data, int $createdBy): Payroll
    {
        $existing = Payroll::where('employee_id', $data['employee_id'])
            ->where('month', $data['month'])
            ->where('year', $data['year'])
            ->first();

        if ($existing) {
            throw new \Exception('Payroll already exists for this employee for ' . date('F', mktime(0, 0, 0, $data['month'], 1)) . ' ' . $data['year'], 422);
        }

        return DB::transaction(function () use ($data, $createdBy) {
            // Auto-fetch active loan deduction
            $loanDeduction = 0;
            $activeLoan    = Loan::where('employee_id', $data['employee_id'])
                ->where('status', Loan::STATUS_ACTIVE)
                ->first();

            if ($activeLoan && $activeLoan->monthly_deduction) {
                $loanDeduction = min($activeLoan->monthly_deduction, $activeLoan->remaining_balance);
            }

            $grossSalary     = $data['gross_salary'];
            $loanDed         = $data['loan_deduction'] ?? $loanDeduction;
            $fineDed         = $data['fine_deduction'] ?? 0;
            $salikDed        = $data['salik_deduction'] ?? 0;
            $penaltyDed      = $data['penalty_deduction'] ?? 0;
            $otherDed        = $data['other_deduction'] ?? 0;
            $totalDeductions = $loanDed + $fineDed + $salikDed + $penaltyDed + $otherDed;
            $netSalary       = $grossSalary - $totalDeductions;

            $payroll = Payroll::create([
                'payroll_id'        => $this->generatePayrollId(),
                'employee_id'       => $data['employee_id'],
                'month'             => $data['month'],
                'year'              => $data['year'],
                'salary_type'       => $data['salary_type'] ?? 'monthly',
                'gross_salary'      => $grossSalary,
                'loan_deduction'    => $loanDed,
                'fine_deduction'    => $fineDed,
                'salik_deduction'   => $salikDed,
                'penalty_deduction' => $penaltyDed,
                'other_deduction'   => $otherDed,
                'total_deductions'  => $totalDeductions,
                'net_salary'        => $netSalary,
                'attendance_days'   => $data['attendance_days'] ?? null,
                'hours_compliance'  => $data['hours_compliance'] ?? null,
                'payment_status'    => Payroll::PAYMENT_STATUS_UNPAID,
                'payroll_status'    => Payroll::STATUS_DRAFT,
                'notes'             => $data['notes'] ?? null,
                'created_by'        => $createdBy,
            ]);

            return $payroll->load(['employee:id,employee_id,name']);
        });
    }

    public function show(Payroll $payroll): Payroll
    {
        return $payroll->load([
            'employee:id,employee_id,name,status,phone,gross_salary',
            'approver:id,name',
            'creator:id,name',
            'loanPayments',
        ]);
    }

    public function update(Payroll $payroll, array $data): Payroll
    {
        if ($payroll->isApproved()) {
            throw new \Exception('Cannot edit an approved payroll.', 422);
        }

        $grossSalary     = $data['gross_salary'] ?? $payroll->gross_salary;
        $loanDed         = $data['loan_deduction'] ?? $payroll->loan_deduction;
        $fineDed         = $data['fine_deduction'] ?? $payroll->fine_deduction;
        $salikDed        = $data['salik_deduction'] ?? $payroll->salik_deduction;
        $penaltyDed      = $data['penalty_deduction'] ?? $payroll->penalty_deduction;
        $otherDed        = $data['other_deduction'] ?? $payroll->other_deduction;
        $totalDeductions = $loanDed + $fineDed + $salikDed + $penaltyDed + $otherDed;
        $netSalary       = $grossSalary - $totalDeductions;

        $payroll->update([
            'gross_salary'      => $grossSalary,
            'loan_deduction'    => $loanDed,
            'fine_deduction'    => $fineDed,
            'salik_deduction'   => $salikDed,
            'penalty_deduction' => $penaltyDed,
            'other_deduction'   => $otherDed,
            'total_deductions'  => $totalDeductions,
            'net_salary'        => $netSalary,
            'attendance_days'   => $data['attendance_days'] ?? $payroll->attendance_days,
            'hours_compliance'  => $data['hours_compliance'] ?? $payroll->hours_compliance,
            'notes'             => $data['notes'] ?? $payroll->notes,
        ]);

        return $payroll->refresh()->load(['employee:id,employee_id,name']);
    }

    public function approve(Payroll $payroll, int $approvedBy): Payroll
    {
        if ($payroll->isApproved()) {
            throw new \Exception('Payroll is already approved.', 422);
        }

        return DB::transaction(function () use ($payroll, $approvedBy) {
            $payroll->update([
                'payroll_status' => Payroll::STATUS_APPROVED,
                'approved_by'    => $approvedBy,
                'approved_at'    => now(),
            ]);

            // Auto-record loan payment if there's loan deduction
            if ($payroll->loan_deduction > 0) {
                $activeLoan = Loan::where('employee_id', $payroll->employee_id)
                    ->where('status', Loan::STATUS_ACTIVE)
                    ->first();

                if ($activeLoan) {
                    $paymentAmount = min($payroll->loan_deduction, $activeLoan->remaining_balance);

                    LoanPayment::create([
                        'loan_id'        => $activeLoan->id,
                        'payment_date'   => now()->setMonth($payroll->month)->setYear($payroll->year)->endOfMonth(),
                        'payment_amount' => $paymentAmount,
                        'payment_method' => LoanPayment::METHOD_PAYROLL,
                        'payroll_id'     => $payroll->id,
                        'notes'          => "Auto-deducted from payroll {$payroll->payroll_id}",
                        'created_by'     => $approvedBy,
                    ]);

                    $newPaid      = $activeLoan->paid_amount + $paymentAmount;
                    $newRemaining = $activeLoan->loan_amount - $newPaid;
                    $newStatus    = $newRemaining <= 0 ? Loan::STATUS_PAID : Loan::STATUS_ACTIVE;

                    $newInstallments = null;
                    if ($activeLoan->remaining_installments !== null) {
                        $newInstallments = max(0, $activeLoan->remaining_installments - 1);
                    }

                    $activeLoan->update([
                        'paid_amount'            => $newPaid,
                        'remaining_balance'      => max(0, $newRemaining),
                        'remaining_installments' => $newInstallments,
                        'status'                 => $newStatus,
                    ]);
                }
            }

            // Auto-mark pending fines as deducted if fine_deduction > 0
            if ($payroll->fine_deduction > 0) {
                \App\Models\Fine::where('employee_id', $payroll->employee_id)
                    ->where('status', \App\Models\Fine::STATUS_PENDING)
                    ->orderBy('fine_date')
                    ->get()
                    ->reduce(function ($remaining, $fine) use ($payroll) {
                        if ($remaining <= 0) return 0;
                        $fine->update([
                            'status'     => \App\Models\Fine::STATUS_DEDUCTED,
                            'payroll_id' => $payroll->id,
                        ]);
                        return $remaining - $fine->amount;
                    }, $payroll->fine_deduction);
            }

            return $payroll->refresh()->load(['employee:id,employee_id,name', 'approver:id,name']);
        });
    }

    public function reject(Payroll $payroll, int $rejectedBy, string $reason = null): Payroll
    {
        if ($payroll->isApproved()) {
            throw new \Exception('Cannot reject an already approved payroll.', 422);
        }

        $payroll->update([
            'payroll_status' => Payroll::STATUS_REJECTED,
            'notes'          => $reason ?? $payroll->notes,
        ]);

        return $payroll->refresh()->load(['employee:id,employee_id,name']);
    }

    public function markPaid(Payroll $payroll): Payroll
    {
        if (!$payroll->isApproved()) {
            throw new \Exception('Only approved payrolls can be marked as paid.', 422);
        }

        $payroll->update(['payment_status' => Payroll::PAYMENT_STATUS_PAID]);
        return $payroll->refresh();
    }

    public function generateSlipPdf(Payroll $payroll): string
    {
        $payroll->load([
            'employee:id,employee_id,name,mobile,status,salary_amount',
            'approver:id,name',
        ]);

        $pdf = Pdf::loadView('pdfs.salary_slip', ['payroll' => $payroll])
            ->setPaper('a4', 'portrait');

        return $pdf->output();
    }

    public function getStats(int $month = null, int $year = null): array
    {
        $month = $month ?? now()->month;
        $year  = $year ?? now()->year;

        $base = fn() => Payroll::where('month', $month)->where('year', $year);

        return [
            'month'            => $month,
            'year'             => $year,
            'total_payrolls'   => $base()->count(),
            'approved'         => $base()->where('payroll_status', 'approved')->count(),
            'draft'            => $base()->where('payroll_status', 'draft')->count(),
            'paid'             => $base()->where('payment_status', 'paid')->count(),
            'unpaid'           => $base()->where('payment_status', 'unpaid')->count(),
            'total_gross'      => $base()->sum('gross_salary'),
            'total_deductions' => $base()->sum('total_deductions'),
            'total_net'        => $base()->sum('net_salary'),
        ];
    }
}
