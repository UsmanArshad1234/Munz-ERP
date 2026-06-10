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

    // POST /api/employees/{employee}/update-documents
    public function updateProfileDocuments(Request $request, Employee $employee): JsonResponse
    {
        $request->validate([
            'passport_document' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'visa_document'     => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        if (!$request->hasFile('passport_document') && !$request->hasFile('visa_document')) {
            return $this->error('At least one document file is required', 422);
        }

        $updated = $this->employeeService->updateProfileDocuments(
            $employee,
            $request->file('passport_document'),
            $request->file('visa_document')
        );

        return $this->success($updated, 'Documents updated successfully');
    }

    // POST /api/employees/{employee}/documents
    public function uploadDocument(Request $request, Employee $employee): JsonResponse
    {
        $request->validate([
            'file'          => 'required|file|max:5120|mimes:jpg,jpeg,png,pdf',
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

        return $this->created($this->formatDocumentResponse($document), 'Document uploaded successfully');
    }

    // POST /api/employees/{employee}/documents/{type}/upload  (upload or replace by type)
    public function uploadByType(Request $request, Employee $employee, string $type): JsonResponse
    {
        if (!in_array($type, EmployeeDocument::TYPES)) {
            return $this->error('Invalid document type. Allowed: ' . implode(', ', EmployeeDocument::TYPES), 422);
        }

        $request->validate([
            'file'        => 'required|file|max:5120|mimes:jpg,jpeg,png,pdf',
            'expiry_date' => 'nullable|date',
        ]);

        $document = $this->employeeService->uploadOrReplaceDocument(
            $employee,
            $request->file('file'),
            $type,
            $request->expiry_date,
            $request->user()->id
        );

        return $this->created($this->formatDocumentResponse($document), ucfirst(str_replace('_', ' ', $type)) . ' uploaded successfully');
    }

    private function formatDocumentResponse(EmployeeDocument $document): array
    {
        return [
            'id'            => $document->id,
            'document_type' => $document->document_type,
            'original_name' => $document->original_name,
            'file_url'      => asset('storage/' . $document->file_path),
            'file_size'     => $document->file_size_human,
            'expiry_date'   => $document->expiry_date?->toDateString(),
            'uploaded_at'   => $document->created_at,
        ];
    }

    // GET /api/employees/{employee}/documents
    public function documents(Employee $employee): JsonResponse
    {
        $employee->load('documents');

        $byType = [];
        foreach (EmployeeDocument::TYPES as $type) {
            $byType[$type] = null;
        }

        foreach ($employee->documents()->orderBy('document_type')->get() as $d) {
            $byType[$d->document_type] = $this->formatDocumentResponse($d);
        }

        return $this->success([
            'documents_by_type' => $byType,
            'all_documents'     => $employee->documents->map(fn($d) => $this->formatDocumentResponse($d))->values(),
        ]);
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
