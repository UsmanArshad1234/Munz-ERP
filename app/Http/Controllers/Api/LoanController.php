<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Loan\CreateLoanRequest;
use App\Http\Requests\Loan\UpdateLoanRequest;
use App\Http\Requests\Loan\RecordPaymentRequest;
use App\Models\Loan;
use App\Services\LoanService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LoanController extends Controller
{
    use ApiResponse;

    public function __construct(private LoanService $loanService) {}

    public function index(Request $request): JsonResponse
    {
        $loans = $this->loanService->list($request->all());
        return $this->success($loans, 'Loans retrieved successfully');
    }

    public function store(CreateLoanRequest $request): JsonResponse
    {
        try {
            // Employee can only have one active loan at a time
            $existing = $this->loanService->getEmployeeActiveLoan($request->employee_id);
            if ($existing) {
                return $this->error("Employee already has an active loan ({$existing->loan_id}). Settle it first.", 422);
            }

            $loan = $this->loanService->create($request->validated(), $request->user()->id);
            return $this->created($loan, 'Loan created successfully');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), ($e->getCode() >= 400 && $e->getCode() < 600 ? (int)$e->getCode() : 500));
        }
    }

    public function show(Loan $loan): JsonResponse
    {
        return $this->success($this->loanService->show($loan), 'Loan retrieved successfully');
    }

    public function update(UpdateLoanRequest $request, Loan $loan): JsonResponse
    {
        try {
            $updated = $this->loanService->update($loan, $request->validated());
            return $this->success($updated, 'Loan updated successfully');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), ($e->getCode() >= 400 && $e->getCode() < 600 ? (int)$e->getCode() : 500));
        }
    }

    public function recordPayment(RecordPaymentRequest $request, Loan $loan): JsonResponse
    {
        try {
            $payment = $this->loanService->recordPayment($loan, $request->validated(), $request->user()->id);
            return $this->created($payment, 'Loan payment recorded successfully');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), ($e->getCode() >= 400 && $e->getCode() < 600 ? (int)$e->getCode() : 500));
        }
    }

    public function payments(Loan $loan): JsonResponse
    {
        $payments = $this->loanService->listPayments($loan);
        return $this->success($payments, 'Loan payments retrieved successfully');
    }

    public function uploadAttachment(Request $request, Loan $loan): JsonResponse
    {
        $request->validate(['attachment' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120']);

        try {
            $path = $this->loanService->uploadAttachment($loan, $request->file('attachment'));
            return $this->success(['attachment_path' => $path], 'Attachment uploaded successfully');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function destroy(Loan $loan, Request $request): JsonResponse
    {
        if (!$request->user()->isOwner()) {
            return $this->error('Unauthorized. Only the owner can delete a loan.', 403);
        }

        try {
            $this->loanService->destroy($loan);
            return $this->success(null, 'Loan deleted successfully');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), ($e->getCode() >= 400 && $e->getCode() < 600 ? (int)$e->getCode() : 500));
        }
    }

    public function stats(): JsonResponse
    {
        return $this->success($this->loanService->getStats(), 'Loan statistics retrieved');
    }
}
