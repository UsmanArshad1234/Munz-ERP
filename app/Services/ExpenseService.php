<?php

namespace App\Services;

use App\Models\Expense;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Pagination\LengthAwarePaginator;

class ExpenseService
{
    public function generateExpenseId(): string
    {
        $last = Expense::orderBy('id', 'desc')->lockForUpdate()->first();
        $next = $last ? ((int) substr($last->expense_id, 4)) + 1 : 1;
        return 'EXP-' . str_pad($next, 4, '0', STR_PAD_LEFT);
    }

    public function list(array $filters): LengthAwarePaginator
    {
        $query = Expense::with(['approver:id,name', 'creator:id,name'])
            ->when($filters['category'] ?? null, fn($q, $v) => $q->where('category', $v))
            ->when($filters['status'] ?? null, fn($q, $v) => $q->where('status', $v))
            ->when($filters['from_date'] ?? null, fn($q, $v) => $q->whereDate('expense_date', '>=', $v))
            ->when($filters['to_date'] ?? null, fn($q, $v) => $q->whereDate('expense_date', '<=', $v))
            ->when($filters['search'] ?? null, function ($q, $search) {
                $q->where(function ($q) use ($search) {
                    $q->where('expense_id', 'like', "%$search%")
                      ->orWhere('description', 'like', "%$search%")
                      ->orWhere('vendor_name', 'like', "%$search%");
                });
            })
            ->orderBy('expense_date', 'desc');

        return $query->paginate($filters['per_page'] ?? 15);
    }

    public function create(array $data, int $createdBy): Expense
    {
        return DB::transaction(function () use ($data, $createdBy) {
            return Expense::create([
                'expense_id'  => $this->generateExpenseId(),
                'expense_date'=> $data['expense_date'],
                'category'    => $data['category'],
                'amount'      => $data['amount'],
                'description' => $data['description'] ?? null,
                'vendor_name' => $data['vendor_name'] ?? null,
                'status'      => Expense::STATUS_PENDING,
                'notes'       => $data['notes'] ?? null,
                'created_by'  => $createdBy,
            ]);
        });
    }

    public function show(Expense $expense): Expense
    {
        return $expense->load(['approver:id,name', 'creator:id,name']);
    }

    public function update(Expense $expense, array $data): Expense
    {
        if ($expense->isApproved()) {
            throw new \Exception('Cannot edit an approved expense.', 422);
        }

        $allowed = ['expense_date', 'category', 'amount', 'description', 'vendor_name', 'notes'];
        $expense->update(array_intersect_key($data, array_flip($allowed)));

        return $expense->refresh();
    }

    public function approve(Expense $expense, int $approvedBy): Expense
    {
        if ($expense->isApproved()) {
            throw new \Exception('Expense is already approved.', 422);
        }

        $expense->update([
            'status'      => Expense::STATUS_APPROVED,
            'approved_by' => $approvedBy,
            'approved_at' => now(),
        ]);

        return $expense->refresh()->load(['approver:id,name']);
    }

    public function reject(Expense $expense, int $rejectedBy, string $reason = null): Expense
    {
        if ($expense->isApproved()) {
            throw new \Exception('Cannot reject an already approved expense.', 422);
        }

        $expense->update([
            'status' => Expense::STATUS_REJECTED,
            'notes'  => $reason ?? $expense->notes,
        ]);

        return $expense->refresh();
    }

    public function destroy(Expense $expense, ?\App\Models\User $user = null): void
    {
        if ($expense->isApproved() && !($user?->isOwnerOrSuperadmin())) {
            throw new \Exception('Cannot delete an approved expense.', 422);
        }

        if ($expense->receipt_path) {
            Storage::disk('public')->delete($expense->receipt_path);
        }

        $expense->delete();
    }

    public function uploadReceipt(Expense $expense, $file): string
    {
        if ($expense->receipt_path) {
            Storage::disk('public')->delete($expense->receipt_path);
        }
        $path = $file->store("expenses/{$expense->id}/receipts", 'public');
        $expense->update(['receipt_path' => $path]);
        return $path;
    }

    public function getStats(array $filters = []): array
    {
        $base = function () use ($filters) {
            return Expense::query()
                ->when($filters['from_date'] ?? null, fn($q, $v) => $q->whereDate('expense_date', '>=', $v))
                ->when($filters['to_date'] ?? null, fn($q, $v) => $q->whereDate('expense_date', '<=', $v));
        };

        $byCategory = Expense::query()
            ->when($filters['from_date'] ?? null, fn($q, $v) => $q->whereDate('expense_date', '>=', $v))
            ->when($filters['to_date'] ?? null, fn($q, $v) => $q->whereDate('expense_date', '<=', $v))
            ->where('status', 'approved')
            ->selectRaw('category, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('category')
            ->get()
            ->keyBy('category');

        return [
            'total'              => $base()->count(),
            'pending'            => $base()->where('status', 'pending')->count(),
            'approved'           => $base()->where('status', 'approved')->count(),
            'rejected'           => $base()->where('status', 'rejected')->count(),
            'total_amount'       => $base()->where('status', 'approved')->sum('amount'),
            'pending_amount'     => $base()->where('status', 'pending')->sum('amount'),
            'by_category'        => $byCategory,
        ];
    }
}
