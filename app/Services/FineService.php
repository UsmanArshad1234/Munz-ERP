<?php

namespace App\Services;

use App\Models\Fine;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Pagination\LengthAwarePaginator;

class FineService
{
    public function generateFineId(): string
    {
        $last = Fine::orderBy('id', 'desc')->lockForUpdate()->first();
        $next = $last ? ((int) substr($last->fine_id, 3)) + 1 : 1;
        return 'FN-' . str_pad($next, 4, '0', STR_PAD_LEFT);
    }

    public function list(array $filters): LengthAwarePaginator
    {
        $query = Fine::with(['employee:id,employee_id,name', 'creator:id,name'])
            ->when($filters['employee_id'] ?? null, fn($q, $v) => $q->where('employee_id', $v))
            ->when($filters['fine_type'] ?? null, fn($q, $v) => $q->where('fine_type', $v))
            ->when($filters['status'] ?? null, fn($q, $v) => $q->where('status', $v))
            ->when($filters['from_date'] ?? null, fn($q, $v) => $q->whereDate('fine_date', '>=', $v))
            ->when($filters['to_date'] ?? null, fn($q, $v) => $q->whereDate('fine_date', '<=', $v))
            ->when($filters['search'] ?? null, function ($q, $search) {
                $q->where(function ($q) use ($search) {
                    $q->where('fine_id', 'like', "%$search%")
                      ->orWhere('description', 'like', "%$search%")
                      ->orWhereHas('employee', fn($eq) => $eq->where('name', 'like', "%$search%"));
                });
            })
            ->orderBy('fine_date', 'desc');

        return $query->paginate($filters['per_page'] ?? 15);
    }

    public function create(array $data, int $createdBy): Fine
    {
        return DB::transaction(function () use ($data, $createdBy) {
            return Fine::create([
                'fine_id'     => $this->generateFineId(),
                'employee_id' => $data['employee_id'],
                'fine_date'   => $data['fine_date'],
                'fine_type'   => $data['fine_type'],
                'amount'      => $data['amount'],
                'description' => $data['description'] ?? null,
                'status'      => Fine::STATUS_PENDING,
                'notes'       => $data['notes'] ?? null,
                'created_by'  => $createdBy,
            ]);
        });
    }

    public function show(Fine $fine): Fine
    {
        return $fine->load(['employee:id,employee_id,name,status', 'payroll:id,payroll_id', 'creator:id,name']);
    }

    public function update(Fine $fine, array $data): Fine
    {
        if ($fine->status === Fine::STATUS_DEDUCTED) {
            throw new \Exception('Cannot edit a fine that has already been deducted from payroll.', 422);
        }

        $allowed = ['fine_date', 'fine_type', 'amount', 'description', 'status', 'notes'];
        $fine->update(array_intersect_key($data, array_flip($allowed)));

        return $fine->refresh()->load(['employee:id,employee_id,name']);
    }

    public function waive(Fine $fine, string $notes = null): Fine
    {
        if ($fine->status === Fine::STATUS_DEDUCTED) {
            throw new \Exception('Cannot waive a fine that has already been deducted.', 422);
        }

        $fine->update([
            'status' => Fine::STATUS_WAIVED,
            'notes'  => $notes ?? $fine->notes,
        ]);

        return $fine->refresh();
    }

    public function destroy(Fine $fine, ?\App\Models\User $user = null): void
    {
        if ($fine->status === Fine::STATUS_DEDUCTED && !($user?->isOwnerOrSuperadmin())) {
            throw new \Exception('Cannot delete a fine that has been deducted from payroll.', 422);
        }

        if ($fine->receipt_path) {
            Storage::disk('public')->delete($fine->receipt_path);
        }

        $fine->delete();
    }

    public function uploadReceipt(Fine $fine, $file): string
    {
        if ($fine->receipt_path) {
            Storage::disk('public')->delete($fine->receipt_path);
        }
        $path = $file->store("fines/{$fine->id}/receipts", 'public');
        $fine->update(['receipt_path' => $path]);
        return $path;
    }

    public function getPendingByEmployee(int $employeeId): \Illuminate\Database\Eloquent\Collection
    {
        return Fine::where('employee_id', $employeeId)
            ->where('status', Fine::STATUS_PENDING)
            ->orderBy('fine_date')
            ->get();
    }

    public function getStats(): array
    {
        $base = fn() => Fine::query();

        return [
            'total'             => $base()->count(),
            'pending'           => $base()->where('status', 'pending')->count(),
            'deducted'          => $base()->where('status', 'deducted')->count(),
            'waived'            => $base()->where('status', 'waived')->count(),
            'total_amount'      => $base()->sum('amount'),
            'pending_amount'    => $base()->where('status', 'pending')->sum('amount'),
            'salik_count'       => $base()->where('fine_type', 'salik')->count(),
            'salik_amount'      => $base()->where('fine_type', 'salik')->sum('amount'),
            'traffic_amount'    => $base()->where('fine_type', 'traffic_fine')->sum('amount'),
            'penalty_amount'    => $base()->where('fine_type', 'company_penalty')->sum('amount'),
        ];
    }
}
