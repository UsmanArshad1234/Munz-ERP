<?php

namespace App\Exports;

use App\Models\Payroll;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PayrollExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    public function __construct(private array $filters = []) {}

    public function query()
    {
        return Payroll::with('employee:id,employee_id,name')
            ->when($this->filters['month'] ?? null, fn($q, $v) => $q->where('month', $v))
            ->when($this->filters['year'] ?? null, fn($q, $v) => $q->where('year', $v))
            ->when($this->filters['payroll_status'] ?? null, fn($q, $v) => $q->where('payroll_status', $v))
            ->orderBy('year', 'desc')->orderBy('month', 'desc');
    }

    public function headings(): array
    {
        return [
            'Payroll ID', 'Employee ID', 'Employee Name', 'Month', 'Year',
            'Salary Type', 'Gross Salary', 'Loan Deduction', 'Fine Deduction',
            'Salik Deduction', 'Penalty', 'Other', 'Total Deductions',
            'Net Salary', 'Attendance Days', 'Hours Compliance',
            'Payroll Status', 'Payment Status',
        ];
    }

    public function map($row): array
    {
        return [
            $row->payroll_id,
            $row->employee->employee_id ?? '',
            $row->employee->name ?? '',
            date('F', mktime(0, 0, 0, $row->month, 1)),
            $row->year,
            ucfirst($row->salary_type),
            $row->gross_salary,
            $row->loan_deduction,
            $row->fine_deduction,
            $row->salik_deduction,
            $row->penalty_deduction,
            $row->other_deduction,
            $row->total_deductions,
            $row->net_salary,
            $row->attendance_days ?? '',
            $row->hours_compliance ?? '',
            ucfirst($row->payroll_status),
            ucfirst($row->payment_status),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '1a3a5c']], 'font' => ['color' => ['rgb' => 'FFFFFF'], 'bold' => true]],
        ];
    }
}
