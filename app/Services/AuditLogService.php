<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class AuditLogService
{
    public function list(array $filters = []): LengthAwarePaginator
    {
        $query = AuditLog::with('user')->latest('created_at');

        if (!empty($filters['model_type'])) {
            $query->where('model_type', $filters['model_type']);
        }

        if (!empty($filters['model_id'])) {
            $query->where('model_id', $filters['model_id']);
        }

        if (!empty($filters['action'])) {
            $query->where('action', $filters['action']);
        }

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('model_ref', 'like', "%{$search}%")
                  ->orWhere('user_name', 'like', "%{$search}%");
            });
        }

        $perPage = min((int) ($filters['per_page'] ?? 20), 100);

        return $query->paginate($perPage);
    }

    public function getForModel(string $modelType, int|string $modelId): Collection
    {
        return AuditLog::with('user')
            ->where('model_type', $modelType)
            ->where('model_id', $modelId)
            ->latest('created_at')
            ->get();
    }

    public function getModelTypes(): array
    {
        return AuditLog::distinct()->orderBy('model_type')->pluck('model_type')->toArray();
    }
}
