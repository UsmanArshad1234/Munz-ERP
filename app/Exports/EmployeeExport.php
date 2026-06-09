<?php

namespace App\Exports;

use App\Models\Employee;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EmployeeExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    public function __construct(private array $filters = []) {}

    public function query()
    {
        return Employee::query()
            ->when($this->filters['status'] ?? null, fn($q, $v) => $q->where('status', $v))
            ->when($this->filters['department'] ?? null, fn($q, $v) => $q->where('department', $v))
            ->orderBy('employee_id');
    }

    public function headings(): array
    {
        return [
            'Employee ID', 'Name', 'Mobile', 'Email', 'Nationality',
            'Job Title', 'Department', 'Status', 'Work Emirate', 'Zone',
            'Platform', 'Platform ID', 'Gross Salary', 'Salary Type',
            'Passport Expiry', 'Emirates ID Expiry', 'Visa Expiry',
            'Labour Card Expiry', 'Driving License Expiry',
        ];
    }

    public function map($emp): array
    {
        return [
            $emp->employee_id,
            $emp->name,
            $emp->mobile,
            $emp->email,
            $emp->nationality,
            $emp->job_title,
            $emp->department,
            ucfirst($emp->status),
            $emp->work_emirate,
            $emp->zone,
            $emp->platform_name,
            $emp->platform_id,
            $emp->salary_amount,
            ucfirst($emp->salary_type),
            $emp->passport_expiry?->format('Y-m-d'),
            $emp->emirates_id_expiry?->format('Y-m-d'),
            $emp->visa_expiry?->format('Y-m-d'),
            $emp->labour_card_expiry?->format('Y-m-d'),
            $emp->driving_license_expiry?->format('Y-m-d'),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '1a3a5c']]],
        ];
    }
}
