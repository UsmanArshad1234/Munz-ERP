<?php

namespace App\Services;

use App\Models\Loan;
use App\Models\LoanPayment;
use App\Models\Employee;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Pagination\LengthAwarePaginator;

class LoanService
{
    public function generateLoanId(): string
    {
        $last = Loan::orderBy('id', 'desc')->lockForUpdate()->first();
        $next = $last ? ((int) substr($last->loan_id, 3)) + 1 : 1;
        return 'LN-' . str_pad($next, 4, '0', STR_PAD_LEFT);
    }

    public function list(array $filters): LengthAwarePaginator
    {
        $query = Loan::with(['employee:id,employee_id,name', 'creator:id,name'])
            ->when($filters['employee_id'] ?? null, fn($q, $v) => $q->where('employee_id', $v))
            ->when($filters['status'] ?? null, fn($q, $v) => $q->where('status', $v))
            ->when($filters['search'] ?? null, function ($q, $search) {
                $q->where(function ($q) use ($search) {
                    $q->where('loan_id', 'like', "%$search%")
                      ->orWhereHas('employee', fn($eq) => $eq->where('name', 'like', "%$search%"));
                });
            })
            ->orderBy('created_at', 'desc');

        return $query->paginate($filters['per_page'] ?? 15);
    }

    public function create(array $data, int $createdBy): Loan
    {
        return DB::transaction(function () use ($data, $createdBy) {
            $loan = Loan::create([
                'loan_id'                => $this->generateLoanId(),
                'employee_id'            => $data['employee_id'],
                'loan_date'              => $data['loan_date'],
                'loan_amount'            => $data['loan_amount'],
                'paid_amount'            => 0,
                'remaining_balance'      => $data['loan_amount'],
                'monthly_deduction'      => $data['monthly_deduction'] ?? null,
                'number_of_installments' => $data['number_of_installments'] ?? null,
                'remaining_installments' => $data['number_of_installments'] ?? null,
                'status'                 => Loan::STATUS_ACTIVE,
                'notes'                  => $data['notes'] ?? null,
                'attachment_path'        => $data['attachment_path'] ?? null,
                'created_by'             => $createdBy,
            ]);

            return $loan->load(['employee:id,employee_id,name']);
        });
    }

    public function show(Loan $loan): Loan
    {
        return $loan->load(['employee:id,employee_id,name,status', 'payments.creator:id,name', 'creator:id,name']);
    }

    public function update(Loan $loan, array $data): Loan
    {
        $allowed = ['loan_date', 'monthly_deduction', 'number_of_installments', 'status', 'notes'];
        $loan->update(array_intersect_key($data, array_flip($allowed)));

        if (isset($data['status']) && $data['status'] === Loan::STATUS_CANCELLED) {
            // Cancellation does not reverse payments already made
        }

        return $loan->refresh()->load(['employee:id,employee_id,name']);
    }

    public function recordPayment(Loan $loan, array $data, int $createdBy): LoanPayment
    {
        if (!$loan->isActive()) {
            throw new \Exception('Cannot record payment for a non-active loan.', 422);
        }

        if ($data['payment_amount'] > $loan->remaining_balance) {
            throw new \Exception('Payment amount exceeds remaining balance of ' . $loan->remaining_balance, 422);
        }

        return DB::transaction(function () use ($loan, $data, $createdBy) {
            $payment = LoanPayment::create([
                'loan_id'        => $loan->id,
                'payment_date'   => $data['payment_date'],
                'payment_amount' => $data['payment_amount'],
                'payment_method' => $data['payment_method'] ?? LoanPayment::METHOD_CASH,
                'payroll_id'     => $data['payroll_id'] ?? null,
                'notes'          => $data['notes'] ?? null,
                'created_by'     => $createdBy,
            ]);

            $newPaid      = $loan->paid_amount + $data['payment_amount'];
            $newRemaining = $loan->loan_amount - $newPaid;
            $newStatus    = $newRemaining <= 0 ? Loan::STATUS_PAID : Loan::STATUS_ACTIVE;

            $newInstallments = null;
            if ($loan->remaining_installments !== null) {
                $newInstallments = max(0, $loan->remaining_installments - 1);
            }

            $loan->update([
                'paid_amount'            => $newPaid,
                'remaining_balance'      => max(0, $newRemaining),
                'remaining_installments' => $newInstallments,
                'status'                 => $newStatus,
            ]);

            return $payment->load('creator:id,name');
        });
    }

    public function listPayments(Loan $loan): array
    {
        return $loan->payments()->with('creator:id,name')->orderBy('payment_date', 'desc')->get()->toArray();
    }

    public function uploadAttachment(Loan $loan, $file): string
    {
        if ($loan->attachment_path) {
            Storage::disk('public')->delete($loan->attachment_path);
        }
        $path = $file->store("loans/{$loan->id}/attachments", 'public');
        $loan->update(['attachment_path' => $path]);
        return $path;
    }

    public function getEmployeeActiveLoan(int $employeeId): ?Loan
    {
        return Loan::where('employee_id', $employeeId)
            ->where('status', Loan::STATUS_ACTIVE)
            ->first();
    }

    public function getStats(): array
    {
        return [
            'total_loans'          => Loan::count(),
            'active_loans'         => Loan::where('status', 'active')->count(),
            'total_disbursed'      => Loan::sum('loan_amount'),
            'total_outstanding'    => Loan::where('status', 'active')->sum('remaining_balance'),
            'total_collected'      => Loan::sum('paid_amount'),
        ];
    }
}
