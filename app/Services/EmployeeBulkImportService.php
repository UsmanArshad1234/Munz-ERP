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
        $seenEmployeeIds = [];
        $seenEmiratesIds = [];
        $seenEmails      = [];
        $seenMobiles     = [];

        foreach ($rows as $index => $row) {
            $rowNum    = $index + 2; // +2 because row 1 = header
            $rowErrors = $this->validateImportRow($row, $rowNum, $seenEmployeeIds, $seenEmiratesIds, $seenEmails, $seenMobiles);

            if (!empty($rowErrors)) {
                $errors[] = ['row' => $rowNum, 'errors' => $rowErrors];
            } else {
                $validRows[] = $row;
                $empId = strtoupper(trim($row['employee_id'] ?? ''));
                if ($empId) {
                    $seenEmployeeIds[] = $empId;
                }
                if (!empty($row['emirates_id'])) {
                    $seenEmiratesIds[] = $row['emirates_id'];
                }
                $email = strtolower(trim($row['email'] ?? ''));
                if ($email) {
                    $seenEmails[] = $email;
                }
                $mobile = preg_replace('/\D/', '', trim($row['mobile'] ?? ''));
                if ($mobile) {
                    $seenMobiles[] = $mobile;
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

    private function validateImportRow(array $row, int $rowNum, array $seenEmployeeIds, array $seenEmiratesIds, array $seenEmails, array $seenMobiles): array
    {
        $errors = [];

        // Required fields
        if (empty(trim($row['name'] ?? ''))) {
            $errors[] = 'Missing name';
        }

        // Employee ID duplicate check (within file and DB)
        $empId = strtoupper(trim($row['employee_id'] ?? ''));
        if ($empId) {
            if (in_array($empId, $seenEmployeeIds)) {
                $errors[] = "Duplicate Employee ID {$empId} in file";
            } elseif (Employee::where('employee_id', $empId)->exists()) {
                $errors[] = "Employee ID {$empId} already exists in system";
            }
        }

        // Mobile: required + duplicate check (within file and DB)
        $mobile = preg_replace('/\D/', '', trim($row['mobile'] ?? ''));
        if (empty($mobile)) {
            $errors[] = 'Missing mobile number';
        } else {
            if (in_array($mobile, $seenMobiles)) {
                $errors[] = "Duplicate mobile number in file";
            } elseif (Employee::whereRaw("REGEXP_REPLACE(mobile, '[^0-9]', '') = ?", [$mobile])->exists()) {
                $errors[] = "Mobile number already exists in system";
            }
        }

        // Emirates ID duplicate check (within file and DB)
        $eid = trim($row['emirates_id'] ?? '');
        if ($eid) {
            if (in_array($eid, $seenEmiratesIds)) {
                $errors[] = "Duplicate Emirates ID {$eid} in file";
            } elseif (Employee::where('emirates_id', $eid)->exists()) {
                $errors[] = "Emirates ID {$eid} already exists in system";
            }
        }

        // Email duplicate check (within file and DB)
        $email = strtolower(trim($row['email'] ?? ''));
        if ($email) {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Invalid email format";
            } elseif (in_array($email, $seenEmails)) {
                $errors[] = "Duplicate email {$email} in file";
            } elseif (Employee::whereRaw('LOWER(email) = ?', [$email])->exists()) {
                $errors[] = "Email {$email} already exists in system";
            }
        }

        // Date field format validation
        foreach (self::DATE_FIELDS as $field) {
            $val = trim($row[$field] ?? '');
            if ($val !== '' && $this->parseDate($val) === false) {
                $errors[] = "Wrong date format in {$field} (use YYYY-MM-DD)";
            }
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

            if (in_array($field, self::DATE_FIELDS)) {
                $data[$field] = $this->parseDate($val);
            } elseif ($field === 'work_emirate') {
                $data[$field] = $this->normalizeEmirate($val);
            } else {
                $data[$field] = $val;
            }
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

            if (in_array($field, self::DATE_FIELDS)) {
                $data[$field] = $this->parseDate($val);
            } elseif ($field === 'work_emirate') {
                $data[$field] = $this->normalizeEmirate($val);
            } else {
                $data[$field] = $val;
            }
        }

        return $data;
    }

    // ── Emirate Normalization ─────────────────────────────────────────────────

    const EMIRATE_MAP = [
        'dubai'          => 'Dubai',
        'abu dhabi'      => 'Abu Dhabi',
        'abu_dhabi'      => 'Abu Dhabi',
        'abudhabi'       => 'Abu Dhabi',
        'sharjah'        => 'Sharjah',
        'ajman'          => 'Ajman',
        'fujairah'       => 'Fujairah',
        'ras al khaimah' => 'Ras Al Khaimah',
        'ras_al_khaimah' => 'Ras Al Khaimah',
        'rasalkhaimah'   => 'Ras Al Khaimah',
        'rak'            => 'Ras Al Khaimah',
        'umm al quwain'  => 'Umm Al Quwain',
        'umm_al_quwain'  => 'Umm Al Quwain',
        'ummalquwain'    => 'Umm Al Quwain',
        'uaq'            => 'Umm Al Quwain',
        'al ain'         => 'Al Ain',
        'al_ain'         => 'Al Ain',
        'alain'          => 'Al Ain',
        'khor fakkan'    => 'Khor Fakkan',
        'khorfakkan'     => 'Khor Fakkan',
        'khor_fakkan'    => 'Khor Fakkan',
        'other'          => 'Other',
    ];

    private function normalizeEmirate(string $value): string
    {
        return self::EMIRATE_MAP[strtolower(trim($value))] ?? $value;
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    // Map Excel column names (after WithHeadingRow snake_case conversion) to DB field names
    const COLUMN_MAP = [
        'platform'    => 'platform_name',
        'gross_salary' => 'salary_amount',
    ];

    private function parseFile(UploadedFile $file): array
    {
        $import = new EmployeeBulkImport();
        Excel::import($import, $file);

        return $import->rows
            ->map(fn($row) => $row instanceof \Illuminate\Support\Collection
                ? $row->toArray()
                : (array) $row)
            ->map(fn($row) => $this->normalizeColumns($row))
            ->filter(fn($row) => !empty(array_filter($row, fn($v) => !is_null($v) && $v !== '')))
            ->values()
            ->toArray();
    }

    private function normalizeColumns(array $row): array
    {
        foreach (self::COLUMN_MAP as $from => $to) {
            if (array_key_exists($from, $row) && !array_key_exists($to, $row)) {
                $row[$to] = $row[$from];
                unset($row[$from]);
            }
        }
        return $row;
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
