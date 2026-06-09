<?php

namespace App\Services;

use App\Models\Maintenance;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Pagination\LengthAwarePaginator;

class MaintenanceService
{
    public function generateMaintenanceId(): string
    {
        $last = Maintenance::orderBy('id', 'desc')->lockForUpdate()->first();
        $next = $last ? ((int) substr($last->maintenance_id, 4)) + 1 : 1;
        return 'MNT-' . str_pad($next, 4, '0', STR_PAD_LEFT);
    }

    public function list(array $filters): LengthAwarePaginator
    {
        $query = Maintenance::with(['motorbike:id,bike_id,plate_number', 'creator:id,name'])
            ->when($filters['motorbike_id'] ?? null, fn($q, $v) => $q->where('motorbike_id', $v))
            ->when($filters['maintenance_type'] ?? null, fn($q, $v) => $q->where('maintenance_type', $v))
            ->when($filters['status'] ?? null, fn($q, $v) => $q->where('status', $v))
            ->when($filters['from_date'] ?? null, fn($q, $v) => $q->whereDate('maintenance_date', '>=', $v))
            ->when($filters['to_date'] ?? null, fn($q, $v) => $q->whereDate('maintenance_date', '<=', $v))
            ->when($filters['search'] ?? null, function ($q, $search) {
                $q->where(function ($q) use ($search) {
                    $q->where('maintenance_id', 'like', "%$search%")
                      ->orWhere('description', 'like', "%$search%")
                      ->orWhere('vendor_name', 'like', "%$search%")
                      ->orWhereHas('motorbike', fn($mq) => $mq->where('plate_number', 'like', "%$search%"));
                });
            })
            ->orderBy('maintenance_date', 'desc');

        return $query->paginate($filters['per_page'] ?? 15);
    }

    public function create(array $data, int $createdBy): Maintenance
    {
        return DB::transaction(function () use ($data, $createdBy) {
            return Maintenance::create([
                'maintenance_id'       => $this->generateMaintenanceId(),
                'motorbike_id'         => $data['motorbike_id'],
                'maintenance_date'     => $data['maintenance_date'],
                'maintenance_type'     => $data['maintenance_type'],
                'cost'                 => $data['cost'] ?? 0,
                'description'          => $data['description'] ?? null,
                'vendor_name'          => $data['vendor_name'] ?? null,
                'next_maintenance_date'=> $data['next_maintenance_date'] ?? null,
                'status'               => $data['status'] ?? Maintenance::STATUS_COMPLETED,
                'notes'                => $data['notes'] ?? null,
                'created_by'           => $createdBy,
            ]);
        });
    }

    public function show(Maintenance $maintenance): Maintenance
    {
        return $maintenance->load(['motorbike:id,bike_id,plate_number,brand,model', 'creator:id,name']);
    }

    public function update(Maintenance $maintenance, array $data): Maintenance
    {
        $allowed = ['maintenance_date', 'maintenance_type', 'cost', 'description', 'vendor_name', 'next_maintenance_date', 'status', 'notes'];
        $maintenance->update(array_intersect_key($data, array_flip($allowed)));
        return $maintenance->refresh()->load(['motorbike:id,bike_id,plate_number']);
    }

    public function destroy(Maintenance $maintenance): void
    {
        if ($maintenance->receipt_path) {
            Storage::disk('public')->delete($maintenance->receipt_path);
        }
        $maintenance->delete();
    }

    public function uploadReceipt(Maintenance $maintenance, $file): string
    {
        if ($maintenance->receipt_path) {
            Storage::disk('public')->delete($maintenance->receipt_path);
        }
        $path = $file->store("maintenance/{$maintenance->id}/receipts", 'public');
        $maintenance->update(['receipt_path' => $path]);
        return $path;
    }

    public function getUpcomingMaintenance(int $days = 30): \Illuminate\Database\Eloquent\Collection
    {
        return Maintenance::with(['motorbike:id,bike_id,plate_number'])
            ->whereNotNull('next_maintenance_date')
            ->whereDate('next_maintenance_date', '<=', now()->addDays($days))
            ->whereDate('next_maintenance_date', '>=', now())
            ->orderBy('next_maintenance_date')
            ->get();
    }

    public function getStats(): array
    {
        $base = fn() => Maintenance::query();

        return [
            'total'               => $base()->count(),
            'completed'           => $base()->where('status', 'completed')->count(),
            'pending'             => $base()->where('status', 'pending')->count(),
            'in_progress'         => $base()->where('status', 'in_progress')->count(),
            'total_cost'          => $base()->sum('cost'),
            'this_month_cost'     => $base()->whereMonth('maintenance_date', now()->month)
                                           ->whereYear('maintenance_date', now()->year)->sum('cost'),
            'upcoming_due'        => Maintenance::whereNotNull('next_maintenance_date')
                                        ->whereDate('next_maintenance_date', '<=', now()->addDays(30))
                                        ->whereDate('next_maintenance_date', '>=', now())
                                        ->count(),
        ];
    }
}
