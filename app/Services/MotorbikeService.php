<?php

namespace App\Services;

use App\Models\Motorbike;
use App\Models\MotorbikeDocument;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;

class MotorbikeService
{
    // ── CRUD ──────────────────────────────────────────────────────────────────

    public function getAll(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        $query = Motorbike::with(['currentRider:id,employee_id,name'])
                          ->withoutTrashed();

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('bike_id', 'like', "%{$filters['search']}%")
                  ->orWhere('plate_number', 'like', "%{$filters['search']}%")
                  ->orWhere('brand', 'like', "%{$filters['search']}%")
                  ->orWhere('model', 'like', "%{$filters['search']}%");
            });
        }

        foreach (['status', 'emirate', 'zone', 'brand', 'bike_type'] as $field) {
            if (!empty($filters[$field])) {
                $query->where($field, $filters[$field]);
            }
        }

        return $query->orderBy('bike_id')->paginate($perPage);
    }

    public function findById(int $id): Motorbike
    {
        return Motorbike::with(['currentRider:id,employee_id,name', 'documents'])->findOrFail($id);
    }

    public function create(array $data, int $createdBy): Motorbike
    {
        $data['bike_id']    = $this->generateBikeId();
        $data['created_by'] = $createdBy;
        $data['status']     = $data['status'] ?? 'available';

        return Motorbike::create($data);
    }

    public function update(Motorbike $bike, array $data): Motorbike
    {
        $bike->update($data);
        return $bike->fresh(['currentRider']);
    }

    public function delete(Motorbike $bike): void
    {
        if ($bike->status === 'assigned') {
            throw new \Exception('Cannot delete a bike that is currently assigned to a rider.');
        }
        $bike->delete();
    }

    // ── Documents ─────────────────────────────────────────────────────────────

    public function uploadDocument(Motorbike $bike, UploadedFile $file, string $documentType, ?string $expiryDate, int $uploadedBy): MotorbikeDocument
    {
        $path = $file->store("motorbikes/{$bike->id}/documents", 'public');

        return MotorbikeDocument::create([
            'motorbike_id'  => $bike->id,
            'document_type' => $documentType,
            'original_name' => $file->getClientOriginalName(),
            'file_path'     => $path,
            'mime_type'     => $file->getMimeType(),
            'file_size'     => $file->getSize(),
            'expiry_date'   => $expiryDate,
            'uploaded_by'   => $uploadedBy,
        ]);
    }

    public function deleteDocument(MotorbikeDocument $document): void
    {
        Storage::disk('public')->delete($document->file_path);
        $document->delete();
    }

    // ── Expiry Alerts ─────────────────────────────────────────────────────────

    public function getExpiryAlerts(int $days = 30): array
    {
        $targetDate = now()->addDays($days)->toDateString();
        $today      = now()->toDateString();

        $expiring = Motorbike::withoutTrashed()
            ->where(function ($q) use ($targetDate, $today) {
                $q->whereBetween('insurance_expiry', [$today, $targetDate])
                  ->orWhereBetween('mulkiya_expiry', [$today, $targetDate]);
            })
            ->get(['id', 'bike_id', 'plate_number', 'brand', 'model', 'insurance_expiry', 'mulkiya_expiry', 'status']);

        $expired = Motorbike::withoutTrashed()
            ->where(function ($q) use ($today) {
                $q->where('insurance_expiry', '<', $today)
                  ->orWhere('mulkiya_expiry', '<', $today);
            })
            ->get(['id', 'bike_id', 'plate_number', 'brand', 'model', 'insurance_expiry', 'mulkiya_expiry', 'status']);

        return [
            'expiring_in_days' => $days,
            'expiring_count'   => $expiring->count(),
            'expired_count'    => $expired->count(),
            'expiring'         => $expiring->map(fn($b) => [
                'id'           => $b->id,
                'bike_id'      => $b->bike_id,
                'plate_number' => $b->plate_number,
                'status'       => $b->status,
                'expiry'       => $b->getExpiryStatus(),
            ]),
            'expired'          => $expired->map(fn($b) => [
                'id'           => $b->id,
                'bike_id'      => $b->bike_id,
                'plate_number' => $b->plate_number,
                'status'       => $b->status,
                'expiry'       => $b->getExpiryStatus(),
            ]),
        ];
    }

    public function getStats(): array
    {
        $base = Motorbike::withoutTrashed();

        return [
            'total'              => (clone $base)->count(),
            'available'          => (clone $base)->where('status', 'available')->count(),
            'assigned'           => (clone $base)->where('status', 'assigned')->count(),
            'under_maintenance'  => (clone $base)->where('status', 'under_maintenance')->count(),
            'damaged'            => (clone $base)->where('status', 'damaged')->count(),
            'inactive'           => (clone $base)->whereIn('status', ['inactive', 'sold', 'cancelled'])->count(),
            'by_emirate'         => (clone $base)->selectRaw('emirate, count(*) as count')
                                                  ->groupBy('emirate')->pluck('count', 'emirate'),
        ];
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function generateBikeId(): string
    {
        $last = Motorbike::withTrashed()->orderByDesc('id')->value('bike_id');
        if (!$last) return 'BK-0001';
        $num = (int) substr($last, 3);
        return 'BK-' . str_pad($num + 1, 4, '0', STR_PAD_LEFT);
    }

    public function formatBike(Motorbike $bike): array
    {
        return [
            'id'                => $bike->id,
            'bike_id'           => $bike->bike_id,
            'plate_number'      => $bike->plate_number,
            'plate_code'        => $bike->plate_code,
            'emirate'           => $bike->emirate,
            'zone'              => $bike->zone,
            'bike_type'         => $bike->bike_type,
            'brand'             => $bike->brand,
            'model'             => $bike->model,
            'year'              => $bike->year,
            'color'             => $bike->color,
            'chassis_number'    => $bike->chassis_number,
            'engine_number'     => $bike->engine_number,
            'insurance_company' => $bike->insurance_company,
            'insurance_expiry'  => $bike->insurance_expiry?->toDateString(),
            'mulkiya_expiry'    => $bike->mulkiya_expiry?->toDateString(),
            'status'            => $bike->status,
            'current_rider'     => $bike->currentRider ? [
                'id'          => $bike->currentRider->id,
                'employee_id' => $bike->currentRider->employee_id,
                'name'        => $bike->currentRider->name,
            ] : null,
            'expiry_status'     => $bike->getExpiryStatus(),
            'notes'             => $bike->notes,
            'documents'         => $bike->documents ?? [],
            'created_at'        => $bike->created_at,
            'updated_at'        => $bike->updated_at,
        ];
    }
}
