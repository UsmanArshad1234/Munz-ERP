<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payroll\CreatePayrollRequest;
use App\Http\Requests\Payroll\UpdatePayrollRequest;
use App\Models\Payroll;
use App\Services\PayrollService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PayrollController extends Controller
{
    use ApiResponse;

    public function __construct(private PayrollService $payrollService) {}

    public function index(Request $request): JsonResponse
    {
        $payrolls = $this->payrollService->list($request->all());
        return $this->success($payrolls, 'Payrolls retrieved successfully');
    }

    public function store(CreatePayrollRequest $request): JsonResponse
    {
        try {
            $payroll = $this->payrollService->create($request->validated(), $request->user()->id);
            return $this->created($payroll, 'Payroll created successfully');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), ($e->getCode() >= 400 && $e->getCode() < 600 ? (int)$e->getCode() : 500));
        }
    }

    public function show(Payroll $payroll): JsonResponse
    {
        return $this->success($this->payrollService->show($payroll), 'Payroll retrieved successfully');
    }

    public function update(UpdatePayrollRequest $request, Payroll $payroll): JsonResponse
    {
        try {
            $updated = $this->payrollService->update($payroll, $request->validated());
            return $this->success($updated, 'Payroll updated successfully');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), ($e->getCode() >= 400 && $e->getCode() < 600 ? (int)$e->getCode() : 500));
        }
    }

    public function approve(Request $request, Payroll $payroll): JsonResponse
    {
        try {
            $approved = $this->payrollService->approve($payroll, $request->user()->id);
            return $this->success($approved, 'Payroll approved successfully');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), ($e->getCode() >= 400 && $e->getCode() < 600 ? (int)$e->getCode() : 500));
        }
    }

    public function reject(Request $request, Payroll $payroll): JsonResponse
    {
        $request->validate(['reason' => 'nullable|string|max:500']);

        try {
            $rejected = $this->payrollService->reject($payroll, $request->user()->id, $request->reason);
            return $this->success($rejected, 'Payroll rejected');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), ($e->getCode() >= 400 && $e->getCode() < 600 ? (int)$e->getCode() : 500));
        }
    }

    public function markPaid(Request $request, Payroll $payroll): JsonResponse
    {
        try {
            $updated = $this->payrollService->markPaid($payroll);
            return $this->success($updated, 'Payroll marked as paid');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), ($e->getCode() >= 400 && $e->getCode() < 600 ? (int)$e->getCode() : 500));
        }
    }

    public function downloadSlip(Payroll $payroll): Response
    {
        try {
            $pdfContent = $this->payrollService->generateSlipPdf($payroll);
            $filename   = "salary-slip-{$payroll->payroll_id}.pdf";

            return response($pdfContent, 200, [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ]);
        } catch (\Exception $e) {
            return response('Failed to generate PDF: ' . $e->getMessage(), 500);
        }
    }

    public function destroy(Payroll $payroll, Request $request): JsonResponse
    {
        if (!$request->user()->isOwner()) {
            return $this->error('Unauthorized. Only the owner can delete a payroll.', 403);
        }

        try {
            $payroll->delete();
            return $this->success(null, 'Payroll deleted successfully');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function stats(Request $request): JsonResponse
    {
        $stats = $this->payrollService->getStats(
            $request->integer('month') ?: null,
            $request->integer('year') ?: null,
        );
        return $this->success($stats, 'Payroll statistics retrieved');
    }
}
