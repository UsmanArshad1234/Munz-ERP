<?php

namespace App\Services;

use App\Models\Assignment;
use App\Models\Employee;
use App\Models\Motorbike;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class AssignmentService
{
    // ── Business Rule Validators ──────────────────────────────────────────────

    public function validateAssign(Employee $employee, Motorbike $bike): ?string
    {
        // Rule 1: Employee must be active
        if ($employee->status !== 'active') {
            return "Employee '{$employee->name}' is not active and cannot be assigned a bike.";
        }

        // Rule 2: Bike must be available
        if ($bike->status !== 'available') {
            return "Bike '{$bike->bike_id}' is not available (current status: {$bike->status}).";
        }

        // Rule 3: Employee cannot have more than one active assignment
        $employeeHasActive = Assignment::where('employee_id', $employee->id)
                                       ->where('status', Assignment::STATUS_ACTIVE)
                                       ->exists();
        if ($employeeHasActive) {
            return "Employee '{$employee->name}' already has an active bike assignment.";
        }

        // Rule 4: Bike cannot have more than one active assignment
        $bikeHasActive = Assignment::where('motorbike_id', $bike->id)
                                   ->where('status', Assignment::STATUS_ACTIVE)
                                   ->exists();
        if ($bikeHasActive) {
            return "Bike '{$bike->bike_id}' is already assigned to another rider.";
        }

        return null; // valid
    }

    // ── Assign Bike ───────────────────────────────────────────────────────────

    public function assign(array $data, int $createdBy): Assignment
    {
        $employee = Employee::findOrFail($data['employee_id']);
        $bike     = Motorbike::findOrFail($data['motorbike_id']);

        $error = $this->validateAssign($employee, $bike);
        if ($error) {
            throw new \Exception($error);
        }

        return DB::transaction(function () use ($data, $employee, $bike, $createdBy) {
            $assignment = Assignment::create([
                'assignment_id'      => $this->generateAssignmentId(),
                'employee_id'        => $employee->id,
                'motorbike_id'       => $bike->id,
                'start_date'         => $data['start_date'] ?? now()->toDateString(),
                'handover_condition' => $data['handover_condition'] ?? null,
                'status'             => Assignment::STATUS_ACTIVE,
                'remarks'            => $data['remarks'] ?? null,
                'created_by'         => $createdBy,
            ]);

            // Update bike status and link rider
            $bike->update([
                'status'           => 'assigned',
                'current_rider_id' => $employee->id,
            ]);

            // Link bike to employee
            $employee->update(['assigned_bike_id' => $bike->id]);

            return $assignment->load(['employee:id,employee_id,name', 'motorbike:id,bike_id,plate_number']);
        });
    }

    // ── Return Bike ───────────────────────────────────────────────────────────

    public function returnBike(Assignment $assignment, array $data, int $updatedBy): Assignment
    {
        if (!$assignment->isActive()) {
            throw new \Exception("This assignment is not active (status: {$assignment->status}). Cannot process return.");
        }

        return DB::transaction(function () use ($assignment, $data, $updatedBy) {
            $assignment->update([
                'status'           => Assignment::STATUS_RETURNED,
                'return_date'      => $data['return_date'] ?? now()->toDateString(),
                'return_condition' => $data['return_condition'] ?? null,
                'remarks'          => $data['remarks'] ?? $assignment->remarks,
                'updated_by'       => $updatedBy,
            ]);

            // Free the bike
            $assignment->motorbike->update([
                'status'           => 'available',
                'current_rider_id' => null,
            ]);

            // Clear employee's bike
            $assignment->employee->update(['assigned_bike_id' => null]);

            return $assignment->fresh(['employee:id,employee_id,name', 'motorbike:id,bike_id,plate_number']);
        });
    }

    // ── Mark Pending Return ───────────────────────────────────────────────────

    public function markPendingReturn(Assignment $assignment, int $updatedBy): Assignment
    {
        if (!$assignment->isActive()) {
            throw new \Exception("Only active assignments can be marked as pending return.");
        }

        $assignment->update([
            'status'     => Assignment::STATUS_PENDING_RETURN,
            'updated_by' => $updatedBy,
        ]);

        return $assignment->fresh();
    }

    // ── Cancel Assignment ─────────────────────────────────────────────────────

    public function cancel(Assignment $assignment, string $remarks = null, int $updatedBy = null): Assignment
    {
        if ($assignment->status === Assignment::STATUS_RETURNED) {
            throw new \Exception("Cannot cancel a returned assignment.");
        }

        return DB::transaction(function () use ($assignment, $remarks, $updatedBy) {
            $assignment->update([
                'status'     => Assignment::STATUS_CANCELLED,
                'remarks'    => $remarks ?? $assignment->remarks,
                'updated_by' => $updatedBy,
            ]);

            if ($assignment->status !== Assignment::STATUS_RETURNED) {
                $assignment->motorbike->update([
                    'status'           => 'available',
                    'current_rider_id' => null,
                ]);
                $assignment->employee->update(['assigned_bike_id' => null]);
            }

            return $assignment->fresh();
        });
    }

    // ── List / History ────────────────────────────────────────────────────────

    public function getAll(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        $query = Assignment::with([
            'employee:id,employee_id,name,platform_name',
            'motorbike:id,bike_id,plate_number,brand,model',
        ]);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['employee_id'])) {
            $query->where('employee_id', $filters['employee_id']);
        }

        if (!empty($filters['motorbike_id'])) {
            $query->where('motorbike_id', $filters['motorbike_id']);
        }

        if (!empty($filters['search'])) {
            $query->whereHas('employee', fn($q) => $q->where('name', 'like', "%{$filters['search']}%"))
                  ->orWhereHas('motorbike', fn($q) => $q->where('plate_number', 'like', "%{$filters['search']}%"));
        }

        return $query->orderByDesc('created_at')->paginate($perPage);
    }

    public function getEmployeeHistory(int $employeeId): \Illuminate\Database\Eloquent\Collection
    {
        return Assignment::with(['motorbike:id,bike_id,plate_number,brand,model'])
                         ->where('employee_id', $employeeId)
                         ->orderByDesc('start_date')
                         ->get();
    }

    public function getBikeHistory(int $bikeId): \Illuminate\Database\Eloquent\Collection
    {
        return Assignment::with(['employee:id,employee_id,name'])
                         ->where('motorbike_id', $bikeId)
                         ->orderByDesc('start_date')
                         ->get();
    }

    public function getStats(): array
    {
        return [
            'active'         => Assignment::where('status', Assignment::STATUS_ACTIVE)->count(),
            'pending_return' => Assignment::where('status', Assignment::STATUS_PENDING_RETURN)->count(),
            'returned'       => Assignment::where('status', Assignment::STATUS_RETURNED)->count(),
            'cancelled'      => Assignment::where('status', Assignment::STATUS_CANCELLED)->count(),
            'total'          => Assignment::count(),
        ];
    }

    public function getCurrentAssignments(): \Illuminate\Database\Eloquent\Collection
    {
        return Assignment::with([
            'employee:id,employee_id,name,mobile,platform_name',
            'motorbike:id,bike_id,plate_number,brand,model,emirate',
        ])->where('status', Assignment::STATUS_ACTIVE)->get();
    }

    public function formatAssignment(Assignment $assignment): array
    {
        return [
            'id'                 => $assignment->id,
            'assignment_id'      => $assignment->assignment_id,
            'employee'           => $assignment->employee ? [
                'id'          => $assignment->employee->id,
                'employee_id' => $assignment->employee->employee_id,
                'name'        => $assignment->employee->name,
            ] : null,
            'motorbike'          => $assignment->motorbike ? [
                'id'           => $assignment->motorbike->id,
                'bike_id'      => $assignment->motorbike->bike_id,
                'plate_number' => $assignment->motorbike->plate_number,
            ] : null,
            'start_date'         => $assignment->start_date?->toDateString(),
            'return_date'        => $assignment->return_date?->toDateString(),
            'handover_condition' => $assignment->handover_condition,
            'return_condition'   => $assignment->return_condition,
            'status'             => $assignment->status,
            'remarks'            => $assignment->remarks,
            'created_at'         => $assignment->created_at,
            'updated_at'         => $assignment->updated_at,
        ];
    }

    public function destroy(Assignment $assignment): void
    {
        // If active assignment, free the bike and rider
        if ($assignment->status === 'active') {
            $assignment->motorbike?->update(['status' => 'available', 'current_rider_id' => null]);
            $assignment->employee?->update(['assigned_bike_id' => null]);
        }
        $assignment->delete();
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function generateAssignmentId(): string
    {
        $last = Assignment::orderByDesc('id')->value('assignment_id');
        if (!$last) return 'ASN-0001';
        $num = (int) substr($last, 4);
        return 'ASN-' . str_pad($num + 1, 4, '0', STR_PAD_LEFT);
    }
}
