<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Fine\CreateFineRequest;
use App\Http\Requests\Fine\UpdateFineRequest;
use App\Models\Fine;
use App\Services\FineService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FineController extends Controller
{
    use ApiResponse;

    public function __construct(private FineService $fineService) {}

    public function index(Request $request): JsonResponse
    {
        return $this->success($this->fineService->list($request->all()), 'Fines retrieved successfully');
    }

    public function store(CreateFineRequest $request): JsonResponse
    {
        try {
            $fine = $this->fineService->create($request->validated(), $request->user()->id);
            return $this->created($fine->load('employee:id,employee_id,name'), 'Fine recorded successfully');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), ($e->getCode() >= 400 && $e->getCode() < 600 ? (int)$e->getCode() : 500));
        }
    }

    public function show(Fine $fine): JsonResponse
    {
        return $this->success($this->fineService->show($fine), 'Fine retrieved successfully');
    }

    public function update(UpdateFineRequest $request, Fine $fine): JsonResponse
    {
        try {
            $updated = $this->fineService->update($fine, $request->validated());
            return $this->success($updated, 'Fine updated successfully');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), ($e->getCode() >= 400 && $e->getCode() < 600 ? (int)$e->getCode() : 500));
        }
    }

    public function waive(Request $request, Fine $fine): JsonResponse
    {
        $request->validate(['notes' => 'nullable|string|max:500']);

        try {
            $waived = $this->fineService->waive($fine, $request->notes);
            return $this->success($waived, 'Fine waived successfully');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), ($e->getCode() >= 400 && $e->getCode() < 600 ? (int)$e->getCode() : 500));
        }
    }

    public function destroy(Fine $fine): JsonResponse
    {
        try {
            $this->fineService->destroy($fine);
            return $this->success(null, 'Fine deleted successfully');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), ($e->getCode() >= 400 && $e->getCode() < 600 ? (int)$e->getCode() : 500));
        }
    }

    public function uploadReceipt(Request $request, Fine $fine): JsonResponse
    {
        $request->validate(['receipt' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120']);

        try {
            $path = $this->fineService->uploadReceipt($fine, $request->file('receipt'));
            return $this->success(['receipt_path' => $path], 'Receipt uploaded successfully');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function pendingByEmployee(int $employeeId): JsonResponse
    {
        $fines = $this->fineService->getPendingByEmployee($employeeId);
        return $this->success($fines, 'Pending fines retrieved');
    }

    public function stats(): JsonResponse
    {
        return $this->success($this->fineService->getStats(), 'Fine statistics retrieved');
    }
}
