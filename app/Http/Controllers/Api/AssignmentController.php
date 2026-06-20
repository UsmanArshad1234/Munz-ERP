<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Assignment\AssignBikeRequest;
use App\Http\Requests\Assignment\ReturnBikeRequest;
use App\Models\Assignment;
use App\Services\AssignmentService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssignmentController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly AssignmentService $assignmentService) {}

    // GET /api/assignments
    public function index(Request $request): JsonResponse
    {
        $assignments = $this->assignmentService->getAll(
            $request->only('status', 'employee_id', 'motorbike_id', 'search'),
            $request->input('per_page', 20)
        );
        return $this->success($assignments);
    }

    // GET /api/assignments/current
    public function current(): JsonResponse
    {
        $assignments = $this->assignmentService->getCurrentAssignments();
        return $this->success($assignments->map(fn($a) => $this->assignmentService->formatAssignment($a)));
    }

    // GET /api/assignments/stats
    public function stats(): JsonResponse
    {
        return $this->success($this->assignmentService->getStats());
    }

    // GET /api/assignments/{assignment}
    public function show(Assignment $assignment): JsonResponse
    {
        $assignment->load(['employee:id,employee_id,name', 'motorbike:id,bike_id,plate_number,brand,model']);
        return $this->success($this->assignmentService->formatAssignment($assignment));
    }

    // POST /api/assignments/assign  ← assign bike to rider
    public function assign(AssignBikeRequest $request): JsonResponse
    {
        try {
            $assignment = $this->assignmentService->assign($request->validated(), $request->user()->id);
            return $this->created($this->assignmentService->formatAssignment($assignment), 'Bike assigned successfully');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 422);
        }
    }

    // POST /api/assignments/{assignment}/return  ← return bike
    public function returnBike(ReturnBikeRequest $request, Assignment $assignment): JsonResponse
    {
        try {
            $updated = $this->assignmentService->returnBike($assignment, $request->validated(), $request->user()->id);
            return $this->success($this->assignmentService->formatAssignment($updated), 'Bike returned successfully');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 422);
        }
    }

    // PATCH /api/assignments/{assignment}/pending-return
    public function markPendingReturn(Assignment $assignment, Request $request): JsonResponse
    {
        try {
            $updated = $this->assignmentService->markPendingReturn($assignment, $request->user()->id);
            return $this->success($this->assignmentService->formatAssignment($updated), 'Marked as pending return');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 422);
        }
    }

    // PATCH /api/assignments/{assignment}/cancel
    public function cancel(Request $request, Assignment $assignment): JsonResponse
    {
        try {
            $updated = $this->assignmentService->cancel(
                $assignment,
                $request->input('remarks'),
                $request->user()->id
            );
            return $this->success($this->assignmentService->formatAssignment($updated), 'Assignment cancelled');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 422);
        }
    }

    // DELETE /api/assignments/{assignment}
    public function destroy(Assignment $assignment, Request $request): JsonResponse
    {
        if (!$request->user()->isOwner()) {
            return $this->error('Unauthorized. Only the owner can delete an assignment.', 403);
        }

        try {
            $this->assignmentService->destroy($assignment);
            return $this->success(null, 'Assignment deleted successfully');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), ($e->getCode() >= 400 && $e->getCode() < 600 ? (int)$e->getCode() : 500));
        }
    }

    // GET /api/assignments/employee/{employeeId}/history
    public function employeeHistory(int $employeeId): JsonResponse
    {
        $history = $this->assignmentService->getEmployeeHistory($employeeId);
        return $this->success($history->map(fn($a) => $this->assignmentService->formatAssignment($a)));
    }

    // GET /api/assignments/bike/{bikeId}/history
    public function bikeHistory(int $bikeId): JsonResponse
    {
        $history = $this->assignmentService->getBikeHistory($bikeId);
        return $this->success($history->map(fn($a) => $this->assignmentService->formatAssignment($a)));
    }
}
