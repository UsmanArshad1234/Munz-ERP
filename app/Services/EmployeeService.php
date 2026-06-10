<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\EmployeeDocument;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;

class EmployeeService
{
    // ── CRUD ──────────────────────────────────────────────────────────────────

    public function getAll(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        $query = Employee::with(['documents' => fn($q) => $q->select('id', 'employee_id', 'document_type', 'expiry_date')])
                         ->withoutTrashed();

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                  ->orWhere('employee_id', 'like', "%{$filters['search']}%")
                  ->orWhere('mobile', 'like', "%{$filters['search']}%")
                  ->orWhere('email', 'like', "%{$filters['search']}%")
                  ->orWhere('platform_id', 'like', "%{$filters['search']}%");
            });
        }

        foreach (['status', 'work_emirate', 'platform_name', 'wps_status', 'department', 'job_title'] as $field) {
            if (!empty($filters[$field])) {
                $query->where($field, $filters[$field]);
            }
        }

        return $query->orderBy('name')->paginate($perPage);
    }

    public function findById(int $id): Employee
    {
        return Employee::with('documents')->findOrFail($id);
    }

    public function create(array $data, int $createdBy): Employee
    {
        // Extract files — we need the employee ID first before storing
        $files = $this->extractDocumentFiles($data);

        $data['employee_id'] = $this->generateEmployeeId();
        $data['created_by']  = $createdBy;
        $data['status']      = $data['status'] ?? 'active';
        $data['wps_status']  = $data['wps_status'] ?? 'no_wps';

        $employee = Employee::create($data);

        // Upload files now that we have the employee ID
        if (!empty($files)) {
            $paths = [];
            foreach ($files as $field => $file) {
                $paths[$field] = $file->store("employees/{$employee->id}/docs", 'public');
            }
            $employee->update($paths);
        }

        return $employee->fresh();
    }

    public function update(Employee $employee, array $data): Employee
    {
        foreach (['passport_document', 'visa_document'] as $field) {
            if (isset($data[$field]) && $data[$field] instanceof \Illuminate\Http\UploadedFile) {
                if ($employee->$field) {
                    Storage::disk('public')->delete($employee->$field);
                }
                $data[$field] = $data[$field]->store("employees/{$employee->id}/docs", 'public');
            } else {
                unset($data[$field]);
            }
        }

        $employee->update($data);
        return $employee->fresh();
    }

    private function formatDocuments(Employee $employee): array
    {
        return ($employee->relationLoaded('documents') ? $employee->documents : collect())
            ->map(fn($d) => [
                'id'            => $d->id,
                'document_type' => $d->document_type,
                'original_name' => $d->original_name,
                'file_url'      => asset('storage/' . $d->file_path),
                'file_size'     => $d->file_size_human,
                'expiry_date'   => $d->expiry_date?->toDateString(),
                'uploaded_at'   => $d->created_at,
            ])->values()->toArray();
    }

    private function formatDocumentsByType(Employee $employee): array
    {
        $docs = ($employee->relationLoaded('documents') ? $employee->documents : collect())
            ->keyBy('document_type');

        $result = [];
        foreach (\App\Models\EmployeeDocument::TYPES as $type) {
            $doc = $docs->get($type);
            $result[$type] = $doc ? [
                'id'            => $doc->id,
                'original_name' => $doc->original_name,
                'file_url'      => asset('storage/' . $doc->file_path),
                'file_size'     => $doc->file_size_human,
                'expiry_date'   => $doc->expiry_date?->toDateString(),
                'uploaded_at'   => $doc->created_at,
            ] : null;
        }

        return $result;
    }

    private function extractDocumentFiles(array &$data): array
    {
        $files = [];
        foreach (['passport_document', 'visa_document'] as $field) {
            if (isset($data[$field]) && $data[$field] instanceof \Illuminate\Http\UploadedFile) {
                $files[$field] = $data[$field];
                unset($data[$field]);
            }
        }
        return $files;
    }

    public function delete(Employee $employee): void
    {
        $employee->delete(); // soft delete
    }

    public function updateProfileDocuments(Employee $employee, ?UploadedFile $passport, ?UploadedFile $visa): array
    {
        $updates = [];

        if ($passport) {
            if ($employee->passport_document) {
                Storage::disk('public')->delete($employee->passport_document);
            }
            $updates['passport_document'] = $passport->store("employees/{$employee->id}/docs", 'public');
        }

        if ($visa) {
            if ($employee->visa_document) {
                Storage::disk('public')->delete($employee->visa_document);
            }
            $updates['visa_document'] = $visa->store("employees/{$employee->id}/docs", 'public');
        }

        if (!empty($updates)) {
            $employee->update($updates);
            $employee->refresh();
        }

        return [
            'passport_document_url' => $employee->passport_document
                                        ? asset('storage/' . $employee->passport_document)
                                        : null,
            'visa_document_url'     => $employee->visa_document
                                        ? asset('storage/' . $employee->visa_document)
                                        : null,
        ];
    }

    // ── Documents ─────────────────────────────────────────────────────────────

    public function uploadOrReplaceDocument(Employee $employee, UploadedFile $file, string $documentType, ?string $expiryDate, int $uploadedBy): EmployeeDocument
    {
        // Delete existing document of this type if present
        $existing = $employee->documents()->where('document_type', $documentType)->first();
        if ($existing) {
            Storage::disk('public')->delete($existing->file_path);
            $existing->delete();
        }

        return $this->uploadDocument($employee, $file, $documentType, $expiryDate, $uploadedBy);
    }

    public function uploadDocument(Employee $employee, UploadedFile $file, string $documentType, ?string $expiryDate, int $uploadedBy): EmployeeDocument
    {
        $path = $file->store("employees/{$employee->id}/documents", 'public');

        return EmployeeDocument::create([
            'employee_id'   => $employee->id,
            'document_type' => $documentType,
            'original_name' => $file->getClientOriginalName(),
            'file_path'     => $path,
            'mime_type'     => $file->getMimeType(),
            'file_size'     => $file->getSize(),
            'expiry_date'   => $expiryDate,
            'uploaded_by'   => $uploadedBy,
        ]);
    }

    public function deleteDocument(EmployeeDocument $document): void
    {
        Storage::disk('public')->delete($document->file_path);
        $document->delete();
    }

    // ── Expiry Alerts ─────────────────────────────────────────────────────────

    public function getExpiryAlerts(int $days = 30): array
    {
        $targetDate = now()->addDays($days)->toDateString();
        $today      = now()->toDateString();

        $expiring = Employee::withoutTrashed()
            ->where(function ($q) use ($targetDate, $today) {
                $q->whereBetween('passport_expiry', [$today, $targetDate])
                  ->orWhereBetween('emirates_id_expiry', [$today, $targetDate])
                  ->orWhereBetween('visa_expiry', [$today, $targetDate])
                  ->orWhereBetween('labour_card_expiry', [$today, $targetDate])
                  ->orWhereBetween('driving_license_expiry', [$today, $targetDate]);
            })
            ->get(['id', 'employee_id', 'name', 'passport_expiry', 'emirates_id_expiry',
                   'visa_expiry', 'labour_card_expiry', 'driving_license_expiry']);

        $expired = Employee::withoutTrashed()
            ->where(function ($q) use ($today) {
                $q->where('passport_expiry', '<', $today)
                  ->orWhere('emirates_id_expiry', '<', $today)
                  ->orWhere('visa_expiry', '<', $today)
                  ->orWhere('labour_card_expiry', '<', $today)
                  ->orWhere('driving_license_expiry', '<', $today);
            })
            ->get(['id', 'employee_id', 'name', 'passport_expiry', 'emirates_id_expiry',
                   'visa_expiry', 'labour_card_expiry', 'driving_license_expiry']);

        return [
            'expiring_in_30_days' => $expiring->count(),
            'expired'             => $expired->count(),
            'expiring_employees'  => $expiring->map(fn($e) => [
                'id'          => $e->id,
                'employee_id' => $e->employee_id,
                'name'        => $e->name,
                'expiry'      => $e->getExpiryStatus(),
            ]),
            'expired_employees'   => $expired->map(fn($e) => [
                'id'          => $e->id,
                'employee_id' => $e->employee_id,
                'name'        => $e->name,
                'expiry'      => $e->getExpiryStatus(),
            ]),
        ];
    }

    public function getStats(): array
    {
        $base = Employee::withoutTrashed();

        return [
            'total'         => $base->count(),
            'active'        => (clone $base)->where('status', 'active')->count(),
            'inactive'      => (clone $base)->where('status', 'inactive')->count(),
            'wps'           => (clone $base)->where('wps_status', 'wps')->count(),
            'no_wps'        => (clone $base)->where('wps_status', 'no_wps')->count(),
            'by_emirate'    => (clone $base)->selectRaw('work_emirate, count(*) as count')
                                            ->groupBy('work_emirate')->pluck('count', 'work_emirate'),
            'by_platform'   => (clone $base)->selectRaw('platform_name, count(*) as count')
                                            ->groupBy('platform_name')->pluck('count', 'platform_name'),
        ];
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function generateEmployeeId(): string
    {
        $last = Employee::withTrashed()->orderByDesc('id')->value('employee_id');

        if (!$last) return 'EMP-0001';

        $num = (int) substr($last, 4);
        return 'EMP-' . str_pad($num + 1, 4, '0', STR_PAD_LEFT);
    }

    public function formatEmployee(Employee $employee): array
    {
        return [
            'id'                      => $employee->id,
            'employee_id'             => $employee->employee_id,
            'name'                    => $employee->name,
            'mobile'                  => $employee->mobile,
            'email'                   => $employee->email,
            'nationality'             => $employee->nationality,
            'job_title'               => $employee->job_title,
            'department'              => $employee->department,
            'status'                  => $employee->status,
            'work_emirate'            => $employee->work_emirate,
            'zone'                    => $employee->zone,
            'platform_name'           => $employee->platform_name,
            'platform_id'             => $employee->platform_id,
            'salary_amount'           => $employee->salary_amount,
            'salary_type'             => $employee->salary_type,
            'wps_status'              => $employee->wps_status,
            'passport_number'         => $employee->passport_number,
            'passport_expiry'         => $employee->passport_expiry?->toDateString(),
            'passport_document_url'   => $employee->passport_document
                                            ? asset('storage/' . $employee->passport_document)
                                            : null,
            'emirates_id'             => $employee->emirates_id,
            'emirates_id_expiry'      => $employee->emirates_id_expiry?->toDateString(),
            'visa_expiry'             => $employee->visa_expiry?->toDateString(),
            'visa_document_url'       => $employee->visa_document
                                            ? asset('storage/' . $employee->visa_document)
                                            : null,
            'labour_card_expiry'      => $employee->labour_card_expiry?->toDateString(),
            'driving_license'         => $employee->driving_license,
            'driving_license_expiry'  => $employee->driving_license_expiry?->toDateString(),
            'assigned_bike_id'        => $employee->assigned_bike_id,
            'notes'                   => $employee->notes,
            'expiry_status'           => $employee->getExpiryStatus(),
            'documents'               => $this->formatDocuments($employee),
            'documents_by_type'       => $this->formatDocumentsByType($employee),
            'created_at'              => $employee->created_at,
            'updated_at'              => $employee->updated_at,
        ];
    }
}
