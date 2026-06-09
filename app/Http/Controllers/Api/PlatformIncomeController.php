<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PlatformIncome\CreateIncomeRequest;
use App\Http\Requests\PlatformIncome\UpdateIncomeRequest;
use App\Models\PlatformIncome;
use App\Services\PlatformIncomeService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlatformIncomeController extends Controller
{
    use ApiResponse;

    public function __construct(private PlatformIncomeService $incomeService) {}

    public function index(Request $request): JsonResponse
    {
        return $this->success($this->incomeService->list($request->all()), 'Income records retrieved successfully');
    }

    public function store(CreateIncomeRequest $request): JsonResponse
    {
        try {
            $income = $this->incomeService->create($request->validated(), $request->user()->id);
            return $this->created($income->load('employee:id,employee_id,name'), 'Income recorded successfully');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function show(PlatformIncome $platformIncome): JsonResponse
    {
        return $this->success($this->incomeService->show($platformIncome), 'Income record retrieved successfully');
    }

    public function update(UpdateIncomeRequest $request, PlatformIncome $platformIncome): JsonResponse
    {
        try {
            $updated = $this->incomeService->update($platformIncome, $request->validated());
            return $this->success($updated, 'Income record updated successfully');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function destroy(PlatformIncome $platformIncome): JsonResponse
    {
        try {
            $this->incomeService->destroy($platformIncome);
            return $this->success(null, 'Income record deleted successfully');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function uploadReceipt(Request $request, PlatformIncome $platformIncome): JsonResponse
    {
        $request->validate(['receipt' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120']);

        try {
            $path = $this->incomeService->uploadReceipt($platformIncome, $request->file('receipt'));
            return $this->success(['receipt_path' => $path], 'Receipt uploaded successfully');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function stats(Request $request): JsonResponse
    {
        $stats = $this->incomeService->getStats($request->only(['from_date', 'to_date', 'month', 'year']));
        return $this->success($stats, 'Income statistics retrieved');
    }

    public function platforms(): JsonResponse
    {
        return $this->success(PlatformIncome::PLATFORMS, 'Platforms list retrieved');
    }
}
