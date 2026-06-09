<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Employee\CreateEmployeeRequest;
use App\Http\Requests\Employee\UpdateEmployeeRequest;
use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Services\EmployeeService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly EmployeeService $employeeService) {}

    // GET /api/employees
    public function index(Request $request): JsonResponse
    {
        $employees = $this->employeeService->getAll(
            $request->only('search', 'status', 'work_emirate', 'platform_name', 'wps_status', 'department', 'job_title'),
            $request->input('per_page', 20)
        );

        return $this->success($employees);
    }

    // GET /api/employees/stats
    public function stats(): JsonResponse
    {
        return $this->success($this->employeeService->getStats());
    }

    // GET /api/employees/expiry-alerts
    public function expiryAlerts(Request $request): JsonResponse
    {
        $days = $request->input('days', 30);
        return $this->success($this->employeeService->getExpiryAlerts($days));
    }

    // POST /api/employees
    public function store(CreateEmployeeRequest $request): JsonResponse
    {
        $employee = $this->employeeService->create(
            $request->validated(),
            $request->user()->id
        );

        return $this->created(
            $this->employeeService->formatEmployee($employee),
            'Employee created successfully'
        );
    }

    // GET /api/employees/{employee}
    public function show(Employee $employee): JsonResponse
    {
        $employee->load('documents');
        return $this->success($this->employeeService->formatEmployee($employee));
    }

    // PUT /api/employees/{employee}
    public function update(UpdateEmployeeRequest $request, Employee $employee): JsonResponse
    {
        $updated = $this->employeeService->update($employee, $request->validated());

        return $this->success(
            $this->employeeService->formatEmployee($updated),
            'Employee updated successfully'
        );
    }

    // DELETE /api/employees/{employee}
    public function destroy(Employee $employee): JsonResponse
    {
        $this->employeeService->delete($employee);
        return $this->success(null, 'Employee deleted successfully');
    }

    // POST /api/employees/{employee}/documents
    public function uploadDocument(Request $request, Employee $employee): JsonResponse
    {
        $request->validate([
            'file'          => 'required|file|max:10240|mimes:jpg,jpeg,png,pdf,doc,docx',
            'document_type' => 'required|in:' . implode(',', EmployeeDocument::TYPES),
            'expiry_date'   => 'nullable|date',
        ]);

        $document = $this->employeeService->uploadDocument(
            $employee,
            $request->file('file'),
            $request->document_type,
            $request->expiry_date,
            $request->user()->id
        );

        return $this->created([
            'id'            => $document->id,
            'document_type' => $document->document_type,
            'original_name' => $document->original_name,
            'file_path'     => asset('storage/' . $document->file_path),
            'file_size'     => $document->file_size_human,
            'expiry_date'   => $document->expiry_date?->toDateString(),
            'uploaded_at'   => $document->created_at,
        ], 'Document uploaded successfully');
    }

    // GET /api/employees/{employee}/documents
    public function documents(Employee $employee): JsonResponse
    {
        $docs = $employee->documents()
            ->orderBy('document_type')
            ->get()
            ->map(fn($d) => [
                'id'            => $d->id,
                'document_type' => $d->document_type,
                'original_name' => $d->original_name,
                'file_url'      => asset('storage/' . $d->file_path),
                'file_size'     => $d->file_size_human,
                'expiry_date'   => $d->expiry_date?->toDateString(),
                'uploaded_at'   => $d->created_at,
            ]);

        return $this->success($docs);
    }

    // DELETE /api/employees/{employee}/documents/{document}
    public function deleteDocument(Employee $employee, EmployeeDocument $document): JsonResponse
    {
        if ($document->employee_id !== $employee->id) {
            return $this->error('Document does not belong to this employee', 400);
        }

        $this->employeeService->deleteDocument($document);
        return $this->success(null, 'Document deleted');
    }
}
