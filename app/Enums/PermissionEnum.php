<?php

namespace App\Enums;

class PermissionEnum
{
    // ── Employees ────────────────────────────────────────────────────────────
    const EMPLOYEES_VIEW   = 'employees.view';
    const EMPLOYEES_CREATE = 'employees.create';
    const EMPLOYEES_UPDATE = 'employees.update';
    const EMPLOYEES_DELETE = 'employees.delete';

    // ── Motorbikes ───────────────────────────────────────────────────────────
    const MOTORBIKES_VIEW   = 'motorbikes.view';
    const MOTORBIKES_CREATE = 'motorbikes.create';
    const MOTORBIKES_UPDATE = 'motorbikes.update';
    const MOTORBIKES_DELETE = 'motorbikes.delete';

    // ── Assignments ──────────────────────────────────────────────────────────
    const ASSIGNMENTS_VIEW   = 'assignments.view';
    const ASSIGNMENTS_CREATE = 'assignments.create';
    const ASSIGNMENTS_UPDATE = 'assignments.update';
    const ASSIGNMENTS_DELETE = 'assignments.delete';

    // ── Payroll ──────────────────────────────────────────────────────────────
    const PAYROLL_VIEW    = 'payroll.view';
    const PAYROLL_CREATE  = 'payroll.create';
    const PAYROLL_UPDATE  = 'payroll.update';
    const PAYROLL_APPROVE = 'payroll.approve';
    const PAYROLL_DELETE  = 'payroll.delete';

    // ── Loans ────────────────────────────────────────────────────────────────
    const LOANS_VIEW   = 'loans.view';
    const LOANS_CREATE = 'loans.create';
    const LOANS_UPDATE = 'loans.update';
    const LOANS_DELETE = 'loans.delete';

    // ── Fines / Salik ────────────────────────────────────────────────────────
    const FINES_VIEW   = 'fines.view';
    const FINES_CREATE = 'fines.create';
    const FINES_UPDATE = 'fines.update';
    const FINES_DELETE = 'fines.delete';

    // ── Expenses ─────────────────────────────────────────────────────────────
    const EXPENSES_VIEW   = 'expenses.view';
    const EXPENSES_CREATE = 'expenses.create';
    const EXPENSES_UPDATE = 'expenses.update';
    const EXPENSES_DELETE = 'expenses.delete';

    // ── Platform Income (owner/superadmin only by default) ───────────────────
    const PLATFORM_INCOME_VIEW   = 'platform_income.view';
    const PLATFORM_INCOME_CREATE = 'platform_income.create';
    const PLATFORM_INCOME_UPDATE = 'platform_income.update';
    const PLATFORM_INCOME_DELETE = 'platform_income.delete';

    // ── Profit & Loss (owner only) ───────────────────────────────────────────
    const PROFIT_LOSS_VIEW = 'profit_loss.view';

    // ── Maintenance ──────────────────────────────────────────────────────────
    const MAINTENANCE_VIEW   = 'maintenance.view';
    const MAINTENANCE_CREATE = 'maintenance.create';
    const MAINTENANCE_UPDATE = 'maintenance.update';
    const MAINTENANCE_DELETE = 'maintenance.delete';

    // ── Documents ────────────────────────────────────────────────────────────
    const DOCUMENTS_VIEW   = 'documents.view';
    const DOCUMENTS_UPLOAD = 'documents.upload';
    const DOCUMENTS_DELETE = 'documents.delete';

    // ── Settings ─────────────────────────────────────────────────────────────
    const SETTINGS_VIEW   = 'settings.view';
    const SETTINGS_MANAGE = 'settings.manage';

    // ── User Management ──────────────────────────────────────────────────────
    const USERS_VIEW   = 'users.view';
    const USERS_CREATE = 'users.create';
    const USERS_UPDATE = 'users.update';
    const USERS_DELETE = 'users.delete';

    // ── Reports ──────────────────────────────────────────────────────────────
    const REPORTS_VIEW      = 'reports.view';
    const REPORTS_EXPORT    = 'reports.export';
    const REPORTS_FINANCIAL = 'reports.financial'; // owner/superadmin only

    // ── Dashboard ────────────────────────────────────────────────────────────
    const DASHBOARD_VIEW      = 'dashboard.view';
    const DASHBOARD_FINANCIAL = 'dashboard.financial'; // owner/superadmin only

    // ── Audit Logs ───────────────────────────────────────────────────────────
    const AUDIT_LOGS_VIEW = 'audit_logs.view';

    // ─────────────────────────────────────────────────────────────────────────

    public static function all(): array
    {
        return [
            // Employees
            ['slug' => self::EMPLOYEES_VIEW,   'name' => 'View Employees',   'module' => 'employees'],
            ['slug' => self::EMPLOYEES_CREATE, 'name' => 'Create Employees', 'module' => 'employees'],
            ['slug' => self::EMPLOYEES_UPDATE, 'name' => 'Update Employees', 'module' => 'employees'],
            ['slug' => self::EMPLOYEES_DELETE, 'name' => 'Delete Employees', 'module' => 'employees'],
            // Motorbikes
            ['slug' => self::MOTORBIKES_VIEW,   'name' => 'View Motorbikes',   'module' => 'motorbikes'],
            ['slug' => self::MOTORBIKES_CREATE, 'name' => 'Create Motorbikes', 'module' => 'motorbikes'],
            ['slug' => self::MOTORBIKES_UPDATE, 'name' => 'Update Motorbikes', 'module' => 'motorbikes'],
            ['slug' => self::MOTORBIKES_DELETE, 'name' => 'Delete Motorbikes', 'module' => 'motorbikes'],
            // Assignments
            ['slug' => self::ASSIGNMENTS_VIEW,   'name' => 'View Assignments',   'module' => 'assignments'],
            ['slug' => self::ASSIGNMENTS_CREATE, 'name' => 'Create Assignments', 'module' => 'assignments'],
            ['slug' => self::ASSIGNMENTS_UPDATE, 'name' => 'Update Assignments', 'module' => 'assignments'],
            ['slug' => self::ASSIGNMENTS_DELETE, 'name' => 'Delete Assignments', 'module' => 'assignments'],
            // Payroll
            ['slug' => self::PAYROLL_VIEW,    'name' => 'View Payroll',    'module' => 'payroll'],
            ['slug' => self::PAYROLL_CREATE,  'name' => 'Create Payroll',  'module' => 'payroll'],
            ['slug' => self::PAYROLL_UPDATE,  'name' => 'Update Payroll',  'module' => 'payroll'],
            ['slug' => self::PAYROLL_APPROVE, 'name' => 'Approve Payroll', 'module' => 'payroll'],
            ['slug' => self::PAYROLL_DELETE,  'name' => 'Delete Payroll',  'module' => 'payroll'],
            // Loans
            ['slug' => self::LOANS_VIEW,   'name' => 'View Loans',   'module' => 'loans'],
            ['slug' => self::LOANS_CREATE, 'name' => 'Create Loans', 'module' => 'loans'],
            ['slug' => self::LOANS_UPDATE, 'name' => 'Update Loans', 'module' => 'loans'],
            ['slug' => self::LOANS_DELETE, 'name' => 'Delete Loans', 'module' => 'loans'],
            // Fines
            ['slug' => self::FINES_VIEW,   'name' => 'View Fines',   'module' => 'fines'],
            ['slug' => self::FINES_CREATE, 'name' => 'Create Fines', 'module' => 'fines'],
            ['slug' => self::FINES_UPDATE, 'name' => 'Update Fines', 'module' => 'fines'],
            ['slug' => self::FINES_DELETE, 'name' => 'Delete Fines', 'module' => 'fines'],
            // Expenses
            ['slug' => self::EXPENSES_VIEW,   'name' => 'View Expenses',   'module' => 'expenses'],
            ['slug' => self::EXPENSES_CREATE, 'name' => 'Create Expenses', 'module' => 'expenses'],
            ['slug' => self::EXPENSES_UPDATE, 'name' => 'Update Expenses', 'module' => 'expenses'],
            ['slug' => self::EXPENSES_DELETE, 'name' => 'Delete Expenses', 'module' => 'expenses'],
            // Platform Income
            ['slug' => self::PLATFORM_INCOME_VIEW,   'name' => 'View Platform Income',   'module' => 'platform_income'],
            ['slug' => self::PLATFORM_INCOME_CREATE, 'name' => 'Create Platform Income', 'module' => 'platform_income'],
            ['slug' => self::PLATFORM_INCOME_UPDATE, 'name' => 'Update Platform Income', 'module' => 'platform_income'],
            ['slug' => self::PLATFORM_INCOME_DELETE, 'name' => 'Delete Platform Income', 'module' => 'platform_income'],
            // Profit & Loss
            ['slug' => self::PROFIT_LOSS_VIEW, 'name' => 'View Profit & Loss', 'module' => 'profit_loss'],
            // Maintenance
            ['slug' => self::MAINTENANCE_VIEW,   'name' => 'View Maintenance',   'module' => 'maintenance'],
            ['slug' => self::MAINTENANCE_CREATE, 'name' => 'Create Maintenance', 'module' => 'maintenance'],
            ['slug' => self::MAINTENANCE_UPDATE, 'name' => 'Update Maintenance', 'module' => 'maintenance'],
            ['slug' => self::MAINTENANCE_DELETE, 'name' => 'Delete Maintenance', 'module' => 'maintenance'],
            // Documents
            ['slug' => self::DOCUMENTS_VIEW,   'name' => 'View Documents',   'module' => 'documents'],
            ['slug' => self::DOCUMENTS_UPLOAD, 'name' => 'Upload Documents', 'module' => 'documents'],
            ['slug' => self::DOCUMENTS_DELETE, 'name' => 'Delete Documents', 'module' => 'documents'],
            // Settings
            ['slug' => self::SETTINGS_VIEW,   'name' => 'View Settings',   'module' => 'settings'],
            ['slug' => self::SETTINGS_MANAGE, 'name' => 'Manage Settings', 'module' => 'settings'],
            // Users
            ['slug' => self::USERS_VIEW,   'name' => 'View Users',   'module' => 'users'],
            ['slug' => self::USERS_CREATE, 'name' => 'Create Users', 'module' => 'users'],
            ['slug' => self::USERS_UPDATE, 'name' => 'Update Users', 'module' => 'users'],
            ['slug' => self::USERS_DELETE, 'name' => 'Delete Users', 'module' => 'users'],
            // Reports
            ['slug' => self::REPORTS_VIEW,      'name' => 'View Reports',               'module' => 'reports'],
            ['slug' => self::REPORTS_EXPORT,    'name' => 'Export Reports',             'module' => 'reports'],
            ['slug' => self::REPORTS_FINANCIAL, 'name' => 'View Financial Reports',     'module' => 'reports'],
            // Dashboard
            ['slug' => self::DASHBOARD_VIEW,      'name' => 'View Dashboard',           'module' => 'dashboard'],
            ['slug' => self::DASHBOARD_FINANCIAL, 'name' => 'View Financial Dashboard', 'module' => 'dashboard'],
            // Audit Logs
            ['slug' => self::AUDIT_LOGS_VIEW, 'name' => 'View Audit Logs', 'module' => 'audit_logs'],
        ];
    }

    // Default permissions per role (owner always gets all — hardcoded in middleware)
    public static function forRole(string $role): array
    {
        return match ($role) {
            'superadmin' => self::superadminPermissions(),
            'admin'      => self::adminPermissions(),
            default      => [],
        };
    }

    private static function superadminPermissions(): array
    {
        // Everything except profit_loss and financial-only views
        $excluded = [self::PROFIT_LOSS_VIEW];

        return array_filter(
            array_column(self::all(), 'slug'),
            fn($slug) => !in_array($slug, $excluded)
        );
    }

    private static function adminPermissions(): array
    {
        return [
            self::EMPLOYEES_VIEW, self::EMPLOYEES_CREATE, self::EMPLOYEES_UPDATE,
            self::MOTORBIKES_VIEW, self::MOTORBIKES_CREATE, self::MOTORBIKES_UPDATE,
            self::ASSIGNMENTS_VIEW, self::ASSIGNMENTS_CREATE, self::ASSIGNMENTS_UPDATE,
            self::PAYROLL_VIEW, self::PAYROLL_CREATE, self::PAYROLL_UPDATE, self::PAYROLL_APPROVE,
            self::LOANS_VIEW, self::LOANS_CREATE, self::LOANS_UPDATE,
            self::FINES_VIEW, self::FINES_CREATE, self::FINES_UPDATE,
            self::EXPENSES_VIEW, self::EXPENSES_CREATE,
            self::MAINTENANCE_VIEW, self::MAINTENANCE_CREATE, self::MAINTENANCE_UPDATE,
            self::DOCUMENTS_VIEW, self::DOCUMENTS_UPLOAD,
            self::SETTINGS_VIEW,
            self::REPORTS_VIEW, self::REPORTS_EXPORT,
            self::DASHBOARD_VIEW,
        ];
    }
}
