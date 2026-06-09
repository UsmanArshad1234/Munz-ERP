<?php

namespace App\Exports;

use App\Models\Expense;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExpenseExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    public function __construct(private array $filters = []) {}

    public function query()
    {
        return Expense::with('approver:id,name')
            ->when($this->filters['category'] ?? null, fn($q, $v) => $q->where('category', $v))
            ->when($this->filters['status'] ?? null, fn($q, $v) => $q->where('status', $v))
            ->when($this->filters['from_date'] ?? null, fn($q, $v) => $q->whereDate('expense_date', '>=', $v))
            ->when($this->filters['to_date'] ?? null, fn($q, $v) => $q->whereDate('expense_date', '<=', $v))
            ->orderBy('expense_date', 'desc');
    }

    public function headings(): array
    {
        return [
            'Expense ID', 'Date', 'Category', 'Amount (AED)',
            'Description', 'Vendor', 'Status', 'Approved By', 'Approved At', 'Notes',
        ];
    }

    public function map($exp): array
    {
        return [
            $exp->expense_id,
            $exp->expense_date->format('Y-m-d'),
            ucfirst(str_replace('_', ' ', $exp->category)),
            $exp->amount,
            $exp->description,
            $exp->vendor_name,
            ucfirst($exp->status),
            $exp->approver->name ?? '',
            $exp->approved_at?->format('Y-m-d'),
            $exp->notes,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '1a3a5c']]],
        ];
    }
}
