<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Expense\CreateExpenseRequest;
use App\Http\Requests\Expense\UpdateExpenseRequest;
use App\Models\Expense;
use App\Services\ExpenseService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    use ApiResponse;

    public function __construct(private ExpenseService $expenseService) {}

    public function index(Request $request): JsonResponse
    {
        return $this->success($this->expenseService->list($request->all()), 'Expenses retrieved successfully');
    }

    public function store(CreateExpenseRequest $request): JsonResponse
    {
        try {
            $expense = $this->expenseService->create($request->validated(), $request->user()->id);
            return $this->created($expense, 'Expense recorded successfully');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), ($e->getCode() >= 400 && $e->getCode() < 600 ? (int)$e->getCode() : 500));
        }
    }

    public function show(Expense $expense): JsonResponse
    {
        return $this->success($this->expenseService->show($expense), 'Expense retrieved successfully');
    }

    public function update(UpdateExpenseRequest $request, Expense $expense): JsonResponse
    {
        try {
            $updated = $this->expenseService->update($expense, $request->validated());
            return $this->success($updated, 'Expense updated successfully');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), ($e->getCode() >= 400 && $e->getCode() < 600 ? (int)$e->getCode() : 500));
        }
    }

    public function approve(Request $request, Expense $expense): JsonResponse
    {
        try {
            $approved = $this->expenseService->approve($expense, $request->user()->id);
            return $this->success($approved, 'Expense approved successfully');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), ($e->getCode() >= 400 && $e->getCode() < 600 ? (int)$e->getCode() : 500));
        }
    }

    public function reject(Request $request, Expense $expense): JsonResponse
    {
        $request->validate(['reason' => 'nullable|string|max:500']);

        try {
            $rejected = $this->expenseService->reject($expense, $request->user()->id, $request->reason);
            return $this->success($rejected, 'Expense rejected');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), ($e->getCode() >= 400 && $e->getCode() < 600 ? (int)$e->getCode() : 500));
        }
    }

    public function destroy(Expense $expense, Request $request): JsonResponse
    {
        try {
            $this->expenseService->destroy($expense, $request->user());
            return $this->success(null, 'Expense deleted successfully');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), ($e->getCode() >= 400 && $e->getCode() < 600 ? (int)$e->getCode() : 500));
        }
    }

    public function uploadReceipt(Request $request, Expense $expense): JsonResponse
    {
        $request->validate(['receipt' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120']);

        try {
            $path = $this->expenseService->uploadReceipt($expense, $request->file('receipt'));
            return $this->success(['receipt_path' => $path], 'Receipt uploaded successfully');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function stats(Request $request): JsonResponse
    {
        $stats = $this->expenseService->getStats($request->only(['from_date', 'to_date']));
        return $this->success($stats, 'Expense statistics retrieved');
    }

    public function categories(): JsonResponse
    {
        return $this->success(Expense::CATEGORIES, 'Expense categories retrieved');
    }
}
