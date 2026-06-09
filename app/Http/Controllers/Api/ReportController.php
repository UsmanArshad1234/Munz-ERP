<?php

namespace App\Http\Controllers\Api;

use App\Exports\EmployeeExport;
use App\Exports\ExpenseExport;
use App\Exports\FineExport;
use App\Exports\PayrollExport;
use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Payroll;
use App\Services\ProfitLossService;
use App\Traits\ApiResponse;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    use ApiResponse;

    public function __construct(private ProfitLossService $plService) {}

    // ── Excel Exports ─────────────────────────────────────────────────────────

    public function employeesExcel(Request $request): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $filename = 'employees-' . now()->format('Y-m-d') . '.xlsx';
        return Excel::download(new EmployeeExport($request->all()), $filename);
    }

    public function payrollExcel(Request $request): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $request->validate([
            'month' => 'nullable|integer|min:1|max:12',
            'year'  => 'nullable|integer|min:2020|max:2099',
        ]);
        $filename = 'payroll-' . ($request->year ?? now()->year) . '-' . ($request->month ?? now()->month) . '.xlsx';
        return Excel::download(new PayrollExport($request->all()), $filename);
    }

    public function expensesExcel(Request $request): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $filename = 'expenses-' . now()->format('Y-m-d') . '.xlsx';
        return Excel::download(new ExpenseExport($request->all()), $filename);
    }

    public function finesExcel(Request $request): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $filename = 'fines-' . now()->format('Y-m-d') . '.xlsx';
        return Excel::download(new FineExport($request->all()), $filename);
    }

    // ── PDF Reports ───────────────────────────────────────────────────────────

    public function payrollPdf(Request $request): Response
    {
        $request->validate([
            'month' => 'nullable|integer|min:1|max:12',
            'year'  => 'nullable|integer|min:2020|max:2099',
        ]);

        $month    = $request->integer('month', now()->month);
        $year     = $request->integer('year', now()->year);
        $payrolls = Payroll::with('employee:id,employee_id,name')
            ->where('month', $month)->where('year', $year)
            ->orderBy('id')
            ->get();

        $pdf      = Pdf::loadView('pdfs.payroll_report', compact('payrolls', 'month', 'year'))
            ->setPaper('a4', 'landscape');
        $filename = "payroll-report-{$year}-{$month}.pdf";

        return response($pdf->output(), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function profitLossPdf(Request $request): Response
    {
        $request->validate([
            'from_date' => 'nullable|date',
            'to_date'   => 'nullable|date',
            'month'     => 'nullable|integer|min:1|max:12',
            'year'      => 'nullable|integer|min:2020|max:2099',
        ]);

        $summary  = $this->plService->getSummary($request->only(['from_date', 'to_date', 'month', 'year']));
        $pdf      = Pdf::loadView('pdfs.profit_loss_report', compact('summary'))
            ->setPaper('a4', 'portrait');
        $filename = 'profit-loss-report-' . now()->format('Y-m-d') . '.pdf';

        return response($pdf->output(), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
