<?php

namespace App\Services;

use App\Imports\EmployeeBulkImport;
use App\Models\Employee;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EmployeeBulkImportService
{
    // Fields allowed in bulk import (header row must use these exact names)
    const IMPORT_FIELDS = [
        'name', 'mobile', 'email', 'nationality',
        'job_title', 'department', 'work_emirate', 'zone',
        'platform_name', 'salary_amount', 'salary_type', 'wps_status',
        'passport_number', 'passport_expiry', 'emirates_id', 'emirates_id_expiry',
        'visa_expiry', 'labour_card_expiry',
        'driving_license', 'driving_license_expiry',
        'status', 'notes',
    ];

    // Fields allowed in bulk update
    const UPDATE_FIELDS = [
        'mobile', 'email', 'work_emirate', 'zone',
        'platform_name', 'salary_amount', 'status',
        'passport_expiry', 'emirates_id_expiry',
        'visa_expiry', 'labour_card_expiry', 'driving_license_expiry',
    ];

    const DATE_FIELDS = [
        'passport_expiry', 'emirates_id_expiry', 'visa_expiry',
        'labour_card_expiry', 'driving_license_expiry',
    ];

    const ERROR_DISK = 'local';
    const ERROR_PATH = 'bulk-errors';

    // ── Public API ────────────────────────────────────────────────────────────

    public function import(UploadedFile $file, int $createdBy): array
    {
        $rows = $this->parseFile($file);

        if (empty($rows)) {
            return [
                'rows_found'           => 0,
                'valid_rows'           => 0,
                'error_rows'           => 0,
                'errors'               => [],
                'error_report_token'   => null,
            ];
        }

        $errors          = [];
        $validRows       = [];
        $seenEmiratesIds = []; // track duplicates within the file

        foreach ($rows as $index => $row) {
            $rowNum    = $index + 2; // +2 because row 1 = header
            $rowErrors = $this->validateImportRow($row, $rowNum, $seenEmiratesIds);

            if (!empty($rowErrors)) {
                $errors[] = ['row' => $rowNum, 'errors' => $rowErrors];
            } else {
                $validRows[] = $row;
                if (!empty($row['emirates_id'])) {
                    $seenEmiratesIds[] = $row['emirates_id'];
                }
            }
        }

        $imported = 0;
        if (!empty($validRows)) {
            DB::transaction(function () use ($validRows, $createdBy, &$imported) {
                $ids = $this->generateEmployeeIds(count($validRows));
                foreach ($validRows as $i => $row) {
                    Employee::create($this->prepareImportData($row, $ids[$i], $createdBy));
                    $imported++;
                }
            });
        }

        $token = !empty($errors) ? $this->storeErrors($errors) : null;

        return [
            'rows_found'         => count($rows),
            'valid_rows'         => $imported,
            'error_rows'         => count($errors),
            'errors'             => $errors,
            'error_report_token' => $token,
        ];
    }

    public function update(UploadedFile $file): array
    {
        $rows = $this->parseFile($file);

        if (empty($rows)) {
            return [
                'rows_found'         => 0,
                'updated_rows'       => 0,
                'error_rows'         => 0,
                'errors'             => [],
                'error_report_token' => null,
            ];
        }

        $errors  = [];
        $updated = 0;

        DB::transaction(function () use ($rows, &$errors, &$updated) {
            foreach ($rows as $index => $row) {
                $rowNum    = $index + 2;
                $rowErrors = $this->validateUpdateRow($row, $rowNum);

                if (!empty($rowErrors)) {
                    $errors[] = ['row' => $rowNum, 'errors' => $rowErrors];
                    continue;
                }

                $employeeId = trim($row['employee_id'] ?? '');
                $employee   = Employee::where('employee_id', $employeeId)->first();

                if (!$employee) {
                    $errors[] = ['row' => $rowNum, 'errors' => ["Employee {$employeeId} not found"]];
                    continue;
                }

                $employee->update($this->prepareUpdateData($row));
                $updated++;
            }
        });

        $token = !empty($errors) ? $this->storeErrors($errors) : null;

        return [
            'rows_found'         => count($rows),
            'updated_rows'       => $updated,
            'error_rows'         => count($errors),
            'errors'             => $errors,
            'error_report_token' => $token,
        ];
    }

    public function downloadErrorReport(string $token): StreamedResponse
    {
        $path = self::ERROR_PATH . '/' . $token . '.json';

        abort_unless(Storage::disk(self::ERROR_DISK)->exists($path), 404, 'Error report not found');

        $errors = json_decode(Storage::disk(self::ERROR_DISK)->get($path), true);

        return response()->streamDownload(function () use ($errors) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Row', 'Errors']);
            foreach ($errors as $item) {
                fputcsv($handle, [
                    $item['row'],
                    implode(' | ', $item['errors']),
                ]);
            }
            fclose($handle);
        }, "error-report-{$token}.csv", ['Content-Type' => 'text/csv']);
    }

    // ── Validation ────────────────────────────────────────────────────────────

    private function validateImportRow(array $row, int $rowNum, array $seenEmiratesIds): array
    {
        $errors = [];

        // Required fields
        if (empty(trim($row['name'] ?? ''))) {
            $errors[] = 'Missing name';
        }
        if (empty(trim($row['mobile'] ?? ''))) {
            $errors[] = 'Missing mobile number';
        }

        // Emirates ID duplicate check (within file)
        $eid = trim($row['emirates_id'] ?? '');
        if ($eid) {
            if (in_array($eid, $seenEmiratesIds)) {
                $errors[] = "Duplicate Emirates ID {$eid} in file";
            } elseif (Employee::where('emirates_id', $eid)->exists()) {
                $errors[] = "Emirates ID {$eid} already exists in system";
            }
        }

        // Date field format validation
        foreach (self::DATE_FIELDS as $field) {
            $val = trim($row[$field] ?? '');
            if ($val !== '' && $this->parseDate($val) === false) {
                $errors[] = "Wrong date format in {$field} (use YYYY-MM-DD)";
            }
        }

        // Email format (optional)
        $email = trim($row['email'] ?? '');
        if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format";
        }

        return $errors;
    }

    private function validateUpdateRow(array $row, int $rowNum): array
    {
        $errors = [];

        if (empty(trim($row['employee_id'] ?? ''))) {
            $errors[] = 'Missing employee_id';
        }

        foreach (self::DATE_FIELDS as $field) {
            $val = trim($row[$field] ?? '');
            if ($val !== '' && $this->parseDate($val) === false) {
                $errors[] = "Wrong date format in {$field} (use YYYY-MM-DD)";
            }
        }

        return $errors;
    }

    // ── Data Preparation ──────────────────────────────────────────────────────

    private function prepareImportData(array $row, string $employeeId, int $createdBy): array
    {
        $data = ['employee_id' => $employeeId, 'created_by' => $createdBy];

        foreach (self::IMPORT_FIELDS as $field) {
            if (!array_key_exists($field, $row)) continue;
            $val = trim((string) ($row[$field] ?? ''));
            if ($val === '') continue;

            $data[$field] = in_array($field, self::DATE_FIELDS)
                ? $this->parseDate($val)
                : $val;
        }

        return $data;
    }

    private function prepareUpdateData(array $row): array
    {
        $data = [];

        foreach (self::UPDATE_FIELDS as $field) {
            if (!array_key_exists($field, $row)) continue;
            $val = trim((string) ($row[$field] ?? ''));
            if ($val === '') continue;

            $data[$field] = in_array($field, self::DATE_FIELDS)
                ? $this->parseDate($val)
                : $val;
        }

        return $data;
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function parseFile(UploadedFile $file): array
    {
        $import = new EmployeeBulkImport();
        Excel::import($import, $file);

        return $import->rows
            ->map(fn($row) => $row instanceof \Illuminate\Support\Collection
                ? $row->toArray()
                : (array) $row)
            ->filter(fn($row) => !empty(array_filter($row, fn($v) => !is_null($v) && $v !== '')))
            ->values()
            ->toArray();
    }

    /**
     * Parse a date value that may be a string, Carbon, or Excel serial number.
     * Returns the date string (Y-m-d), null for empty, or false for invalid.
     */
    private function parseDate(mixed $value): string|null|false
    {
        if ($value === null || $value === '') return null;

        // Carbon / DateTime instance (from Excel reader)
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        $str = trim((string) $value);
        if ($str === '') return null;

        // Excel serial number
        if (is_numeric($str) && (float) $str > 1000) {
            try {
                $dt = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float) $str);
                return $dt->format('Y-m-d');
            } catch (\Throwable) {}
        }

        // Try common string formats
        foreach (['Y-m-d', 'd/m/Y', 'm/d/Y', 'd-m-Y', 'd.m.Y'] as $fmt) {
            $dt = \DateTime::createFromFormat($fmt, $str);
            if ($dt && $dt->format($fmt) === $str) {
                return $dt->format('Y-m-d');
            }
        }

        return false; // invalid
    }

    private function generateEmployeeIds(int $count): array
    {
        $last = Employee::withTrashed()->orderByDesc('id')->value('employee_id');
        $num  = $last ? (int) substr($last, 4) : 0;

        return array_map(
            fn($i) => 'EMP-' . str_pad($num + $i, 4, '0', STR_PAD_LEFT),
            range(1, $count)
        );
    }

    private function storeErrors(array $errors): string
    {
        $token = Str::uuid()->toString();
        Storage::disk(self::ERROR_DISK)->put(
            self::ERROR_PATH . '/' . $token . '.json',
            json_encode($errors)
        );
        return $token;
    }
}
