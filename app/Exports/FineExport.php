<?php

namespace App\Exports;

use App\Models\Fine;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class FineExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    public function __construct(private array $filters = []) {}

    public function query()
    {
        return Fine::with('employee:id,employee_id,name')
            ->when($this->filters['employee_id'] ?? null, fn($q, $v) => $q->where('employee_id', $v))
            ->when($this->filters['fine_type'] ?? null, fn($q, $v) => $q->where('fine_type', $v))
            ->when($this->filters['status'] ?? null, fn($q, $v) => $q->where('status', $v))
            ->when($this->filters['from_date'] ?? null, fn($q, $v) => $q->whereDate('fine_date', '>=', $v))
            ->when($this->filters['to_date'] ?? null, fn($q, $v) => $q->whereDate('fine_date', '<=', $v))
            ->orderBy('fine_date', 'desc');
    }

    public function headings(): array
    {
        return [
            'Fine ID', 'Employee ID', 'Employee Name', 'Date', 'Type',
            'Amount (AED)', 'Description', 'Status', 'Notes',
        ];
    }

    public function map($fine): array
    {
        return [
            $fine->fine_id,
            $fine->employee->employee_id ?? '',
            $fine->employee->name ?? '',
            $fine->fine_date->format('Y-m-d'),
            ucfirst(str_replace('_', ' ', $fine->fine_type)),
            $fine->amount,
            $fine->description,
            ucfirst($fine->status),
            $fine->notes,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '1a3a5c']]],
        ];
    }
}
