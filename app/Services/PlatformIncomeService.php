<?php

namespace App\Services;

use App\Models\PlatformIncome;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Pagination\LengthAwarePaginator;

class PlatformIncomeService
{
    public function generateIncomeId(): string
    {
        $last = PlatformIncome::orderBy('id', 'desc')->lockForUpdate()->first();
        $next = $last ? ((int) substr($last->income_id, 4)) + 1 : 1;
        return 'INC-' . str_pad($next, 4, '0', STR_PAD_LEFT);
    }

    public function list(array $filters): LengthAwarePaginator
    {
        $query = PlatformIncome::with(['employee:id,employee_id,name', 'creator:id,name'])
            ->when($filters['source_type'] ?? null, fn($q, $v) => $q->where('source_type', $v))
            ->when($filters['platform_name'] ?? null, fn($q, $v) => $q->where('platform_name', $v))
            ->when($filters['employee_id'] ?? null, fn($q, $v) => $q->where('employee_id', $v))
            ->when($filters['from_date'] ?? null, fn($q, $v) => $q->whereDate('income_date', '>=', $v))
            ->when($filters['to_date'] ?? null, fn($q, $v) => $q->whereDate('income_date', '<=', $v))
            ->when($filters['month'] ?? null, fn($q, $v) => $q->whereMonth('income_date', $v))
            ->when($filters['year'] ?? null, fn($q, $v) => $q->whereYear('income_date', $v))
            ->when($filters['search'] ?? null, function ($q, $search) {
                $q->where(function ($q) use ($search) {
                    $q->where('income_id', 'like', "%$search%")
                      ->orWhere('description', 'like', "%$search%")
                      ->orWhere('platform_name', 'like', "%$search%")
                      ->orWhereHas('employee', fn($eq) => $eq->where('name', 'like', "%$search%"));
                });
            })
            ->orderBy('income_date', 'desc');

        return $query->paginate($filters['per_page'] ?? 15);
    }

    public function create(array $data, int $createdBy): PlatformIncome
    {
        return DB::transaction(function () use ($data, $createdBy) {
            return PlatformIncome::create([
                'income_id'     => $this->generateIncomeId(),
                'income_date'   => $data['income_date'],
                'source_type'   => $data['source_type'],
                'platform_name' => $data['platform_name'] ?? null,
                'employee_id'   => $data['employee_id'] ?? null,
                'amount'        => $data['amount'],
                'description'   => $data['description'] ?? null,
                'notes'         => $data['notes'] ?? null,
                'created_by'    => $createdBy,
            ]);
        });
    }

    public function show(PlatformIncome $income): PlatformIncome
    {
        return $income->load(['employee:id,employee_id,name', 'creator:id,name']);
    }

    public function update(PlatformIncome $income, array $data): PlatformIncome
    {
        $allowed = ['income_date', 'source_type', 'platform_name', 'employee_id', 'amount', 'description', 'notes'];
        $income->update(array_intersect_key($data, array_flip($allowed)));
        return $income->refresh()->load(['employee:id,employee_id,name']);
    }

    public function destroy(PlatformIncome $income): void
    {
        if ($income->receipt_path) {
            Storage::disk('public')->delete($income->receipt_path);
        }
        $income->delete();
    }

    public function uploadReceipt(PlatformIncome $income, $file): string
    {
        if ($income->receipt_path) {
            Storage::disk('public')->delete($income->receipt_path);
        }
        $path = $file->store("incomes/{$income->id}/receipts", 'public');
        $income->update(['receipt_path' => $path]);
        return $path;
    }

    public function getStats(array $filters = []): array
    {
        $base = function () use ($filters) {
            return PlatformIncome::query()
                ->when($filters['from_date'] ?? null, fn($q, $v) => $q->whereDate('income_date', '>=', $v))
                ->when($filters['to_date'] ?? null, fn($q, $v) => $q->whereDate('income_date', '<=', $v))
                ->when($filters['month'] ?? null, fn($q, $v) => $q->whereMonth('income_date', $v))
                ->when($filters['year'] ?? null, fn($q, $v) => $q->whereYear('income_date', $v));
        };

        $byPlatform = PlatformIncome::query()
            ->where('source_type', PlatformIncome::SOURCE_PLATFORM)
            ->when($filters['from_date'] ?? null, fn($q, $v) => $q->whereDate('income_date', '>=', $v))
            ->when($filters['to_date'] ?? null, fn($q, $v) => $q->whereDate('income_date', '<=', $v))
            ->when($filters['month'] ?? null, fn($q, $v) => $q->whereMonth('income_date', $v))
            ->when($filters['year'] ?? null, fn($q, $v) => $q->whereYear('income_date', $v))
            ->selectRaw('platform_name, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('platform_name')
            ->get();

        return [
            'total_income'      => $base()->sum('amount'),
            'platform_income'   => $base()->where('source_type', 'platform')->sum('amount'),
            'rider_income'      => $base()->where('source_type', 'rider')->sum('amount'),
            'total_records'     => $base()->count(),
            'by_platform'       => $byPlatform,
        ];
    }
}
