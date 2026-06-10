<?php

use App\Http\Controllers\Api\AuditLogController;
use App\Http\Controllers\Api\AssignmentController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ExpenseController;
use App\Http\Controllers\Api\FineController;
use App\Http\Controllers\Api\LoanController;
use App\Http\Controllers\Api\MaintenanceController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\PlatformIncomeController;
use App\Http\Controllers\Api\ProfitLossController;
use App\Http\Controllers\Api\MotorbikeController;
use App\Http\Controllers\Api\PayrollController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\UserManagementController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| MUZN ERP API Routes
|--------------------------------------------------------------------------
| Auth: Bearer token via Laravel Sanctum
| Roles: owner | superadmin | admin
| Permissions: permission:{slug} middleware on protected routes
*/

// ── Public ───────────────────────────────────────────────────────────────────
Route::post('auth/login', [AuthController::class, 'login'])->middleware('throttle:5,1');

// ── Authenticated ─────────────────────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    // ── Auth ──────────────────────────────────────────────────────────────────
    Route::prefix('auth')->group(function () {
        Route::post('logout',         [AuthController::class, 'logout']);
        Route::post('logout-all',     [AuthController::class, 'logoutAll']);
        Route::get('me',              [AuthController::class, 'me']);
        Route::put('profile',         [AuthController::class, 'updateProfile']);
        Route::put('change-password', [AuthController::class, 'changePassword']);
        Route::get('my-permissions',  [PermissionController::class, 'myPermissions']);
    });

    // ── Settings (view = any auth, manage = owner/superadmin) ────────────────
    Route::prefix('settings')->group(function () {
        Route::get('/',             [SettingController::class, 'index']);
        Route::get('types',         [SettingController::class, 'types']);
        Route::get('{type}',        [SettingController::class, 'byType']);

        Route::middleware('permission:settings.manage')->group(function () {
            Route::post('/',                   [SettingController::class, 'store']);
            Route::put('{setting}',            [SettingController::class, 'update']);
            Route::delete('{setting}',         [SettingController::class, 'destroy']);
            Route::put('{type}/reorder',       [SettingController::class, 'reorder']);
        });
    });

    // ── Employees ─────────────────────────────────────────────────────────────
    Route::prefix('employees')->group(function () {
        Route::get('/',                [EmployeeController::class, 'index'])
             ->middleware('permission:employees.view');
        Route::get('stats',            [EmployeeController::class, 'stats'])
             ->middleware('permission:employees.view');
        Route::get('expiry-alerts',    [EmployeeController::class, 'expiryAlerts'])
             ->middleware('permission:employees.view');
        Route::post('/',               [EmployeeController::class, 'store'])
             ->middleware('permission:employees.create');
        Route::get('{employee}',       [EmployeeController::class, 'show'])
             ->middleware('permission:employees.view');
        Route::put('{employee}',       [EmployeeController::class, 'update'])
             ->middleware('permission:employees.update');
        Route::delete('{employee}',    [EmployeeController::class, 'destroy'])
             ->middleware('permission:employees.delete');
        Route::post('{employee}/update-documents', [EmployeeController::class, 'updateProfileDocuments'])
             ->middleware('permission:employees.update');

        // Documents
        Route::get('{employee}/documents',                        [EmployeeController::class, 'documents'])
             ->middleware('permission:documents.view');
        Route::post('{employee}/documents',                       [EmployeeController::class, 'uploadDocument'])
             ->middleware('permission:documents.upload');
        Route::post('{employee}/documents/{type}/upload',         [EmployeeController::class, 'uploadByType'])
             ->middleware('permission:documents.upload');
        Route::delete('{employee}/documents/{document}',          [EmployeeController::class, 'deleteDocument'])
             ->middleware('permission:documents.delete');
    });

    // ── Motorbikes ────────────────────────────────────────────────────────────
    Route::prefix('motorbikes')->group(function () {
        Route::get('/',                  [MotorbikeController::class, 'index'])
             ->middleware('permission:motorbikes.view');
        Route::get('stats',              [MotorbikeController::class, 'stats'])
             ->middleware('permission:motorbikes.view');
        Route::get('expiry-alerts',      [MotorbikeController::class, 'expiryAlerts'])
             ->middleware('permission:motorbikes.view');
        Route::post('/',                 [MotorbikeController::class, 'store'])
             ->middleware('permission:motorbikes.create');
        Route::get('{motorbike}',        [MotorbikeController::class, 'show'])
             ->middleware('permission:motorbikes.view');
        Route::put('{motorbike}',        [MotorbikeController::class, 'update'])
             ->middleware('permission:motorbikes.update');
        Route::delete('{motorbike}',     [MotorbikeController::class, 'destroy'])
             ->middleware('permission:motorbikes.delete');

        // Bike Documents
        Route::get('{motorbike}/documents',              [MotorbikeController::class, 'documents'])
             ->middleware('permission:documents.view');
        Route::post('{motorbike}/documents',             [MotorbikeController::class, 'uploadDocument'])
             ->middleware('permission:documents.upload');
        Route::delete('{motorbike}/documents/{document}',[MotorbikeController::class, 'deleteDocument'])
             ->middleware('permission:documents.delete');
    });

    // ── Assignments ───────────────────────────────────────────────────────────
    Route::prefix('assignments')->group(function () {
        Route::get('/',                                        [AssignmentController::class, 'index'])
             ->middleware('permission:assignments.view');
        Route::get('current',                                  [AssignmentController::class, 'current'])
             ->middleware('permission:assignments.view');
        Route::get('stats',                                    [AssignmentController::class, 'stats'])
             ->middleware('permission:assignments.view');
        Route::post('assign',                                  [AssignmentController::class, 'assign'])
             ->middleware('permission:assignments.create');
        Route::get('{assignment}',                             [AssignmentController::class, 'show'])
             ->middleware('permission:assignments.view');
        Route::post('{assignment}/return',                     [AssignmentController::class, 'returnBike'])
             ->middleware('permission:assignments.update');
        Route::patch('{assignment}/pending-return',            [AssignmentController::class, 'markPendingReturn'])
             ->middleware('permission:assignments.update');
        Route::patch('{assignment}/cancel',                    [AssignmentController::class, 'cancel'])
             ->middleware('permission:assignments.update');
        Route::get('employee/{employeeId}/history',            [AssignmentController::class, 'employeeHistory'])
             ->middleware('permission:assignments.view');
        Route::get('bike/{bikeId}/history',                    [AssignmentController::class, 'bikeHistory'])
             ->middleware('permission:assignments.view');
    });

    // ── Loans ─────────────────────────────────────────────────────────────────
    Route::prefix('loans')->group(function () {
        Route::get('stats',                              [LoanController::class, 'stats'])
             ->middleware('permission:loans.view');
        Route::get('/',                                  [LoanController::class, 'index'])
             ->middleware('permission:loans.view');
        Route::post('/',                                 [LoanController::class, 'store'])
             ->middleware('permission:loans.create');
        Route::get('{loan}',                             [LoanController::class, 'show'])
             ->middleware('permission:loans.view');
        Route::put('{loan}',                             [LoanController::class, 'update'])
             ->middleware('permission:loans.update');
        Route::post('{loan}/payments',                   [LoanController::class, 'recordPayment'])
             ->middleware('permission:loans.update');
        Route::get('{loan}/payments',                    [LoanController::class, 'payments'])
             ->middleware('permission:loans.view');
        Route::post('{loan}/attachment',                 [LoanController::class, 'uploadAttachment'])
             ->middleware('permission:loans.update');
    });

    // ── Payroll ───────────────────────────────────────────────────────────────
    Route::prefix('payroll')->group(function () {
        Route::get('stats',                              [PayrollController::class, 'stats'])
             ->middleware('permission:payroll.view');
        Route::get('/',                                  [PayrollController::class, 'index'])
             ->middleware('permission:payroll.view');
        Route::post('/',                                 [PayrollController::class, 'store'])
             ->middleware('permission:payroll.create');
        Route::get('{payroll}',                          [PayrollController::class, 'show'])
             ->middleware('permission:payroll.view');
        Route::put('{payroll}',                          [PayrollController::class, 'update'])
             ->middleware('permission:payroll.update');
        Route::post('{payroll}/approve',                 [PayrollController::class, 'approve'])
             ->middleware('permission:payroll.approve');
        Route::post('{payroll}/reject',                  [PayrollController::class, 'reject'])
             ->middleware('permission:payroll.approve');
        Route::patch('{payroll}/mark-paid',              [PayrollController::class, 'markPaid'])
             ->middleware('permission:payroll.update');
        Route::get('{payroll}/slip',                     [PayrollController::class, 'downloadSlip'])
             ->middleware('permission:payroll.view');
    });

    // ── Fines ─────────────────────────────────────────────────────────────────
    Route::prefix('fines')->group(function () {
        Route::get('stats',                                  [FineController::class, 'stats'])
             ->middleware('permission:fines.view');
        Route::get('/',                                      [FineController::class, 'index'])
             ->middleware('permission:fines.view');
        Route::post('/',                                     [FineController::class, 'store'])
             ->middleware('permission:fines.create');
        Route::get('employee/{employeeId}/pending',          [FineController::class, 'pendingByEmployee'])
             ->middleware('permission:fines.view');
        Route::get('{fine}',                                 [FineController::class, 'show'])
             ->middleware('permission:fines.view');
        Route::put('{fine}',                                 [FineController::class, 'update'])
             ->middleware('permission:fines.update');
        Route::patch('{fine}/waive',                         [FineController::class, 'waive'])
             ->middleware('permission:fines.update');
        Route::delete('{fine}',                              [FineController::class, 'destroy'])
             ->middleware('permission:fines.delete');
        Route::post('{fine}/receipt',                        [FineController::class, 'uploadReceipt'])
             ->middleware('permission:fines.update');
    });

    // ── Expenses ──────────────────────────────────────────────────────────────
    Route::prefix('expenses')->group(function () {
        Route::get('stats',                                  [ExpenseController::class, 'stats'])
             ->middleware('permission:expenses.view');
        Route::get('categories',                             [ExpenseController::class, 'categories'])
             ->middleware('permission:expenses.view');
        Route::get('/',                                      [ExpenseController::class, 'index'])
             ->middleware('permission:expenses.view');
        Route::post('/',                                     [ExpenseController::class, 'store'])
             ->middleware('permission:expenses.create');
        Route::get('{expense}',                              [ExpenseController::class, 'show'])
             ->middleware('permission:expenses.view');
        Route::put('{expense}',                              [ExpenseController::class, 'update'])
             ->middleware('permission:expenses.update');
        Route::post('{expense}/approve',                     [ExpenseController::class, 'approve'])
             ->middleware('permission:expenses.update');
        Route::post('{expense}/reject',                      [ExpenseController::class, 'reject'])
             ->middleware('permission:expenses.update');
        Route::delete('{expense}',                           [ExpenseController::class, 'destroy'])
             ->middleware('permission:expenses.delete');
        Route::post('{expense}/receipt',                     [ExpenseController::class, 'uploadReceipt'])
             ->middleware('permission:expenses.update');
    });

    // ── Dashboard ─────────────────────────────────────────────────────────────
    Route::prefix('dashboard')->middleware('permission:dashboard.view')->group(function () {
        Route::get('overview', [DashboardController::class, 'overview']);
        Route::get('alerts',   [DashboardController::class, 'alerts']);
    });

    // ── Maintenance ───────────────────────────────────────────────────────────
    Route::prefix('maintenance')->group(function () {
        Route::get('stats',                                  [MaintenanceController::class, 'stats'])
             ->middleware('permission:maintenance.view');
        Route::get('upcoming',                               [MaintenanceController::class, 'upcoming'])
             ->middleware('permission:maintenance.view');
        Route::get('/',                                      [MaintenanceController::class, 'index'])
             ->middleware('permission:maintenance.view');
        Route::post('/',                                     [MaintenanceController::class, 'store'])
             ->middleware('permission:maintenance.create');
        Route::get('{maintenance}',                          [MaintenanceController::class, 'show'])
             ->middleware('permission:maintenance.view');
        Route::put('{maintenance}',                          [MaintenanceController::class, 'update'])
             ->middleware('permission:maintenance.update');
        Route::delete('{maintenance}',                       [MaintenanceController::class, 'destroy'])
             ->middleware('permission:maintenance.delete');
        Route::post('{maintenance}/receipt',                 [MaintenanceController::class, 'uploadReceipt'])
             ->middleware('permission:maintenance.update');
    });

    // ── Reports ───────────────────────────────────────────────────────────────
    Route::prefix('reports')->group(function () {
        // Excel exports
        Route::get('employees/excel',  [ReportController::class, 'employeesExcel'])
             ->middleware('permission:reports.export');
        Route::get('payroll/excel',    [ReportController::class, 'payrollExcel'])
             ->middleware('permission:reports.export');
        Route::get('expenses/excel',   [ReportController::class, 'expensesExcel'])
             ->middleware('permission:reports.export');
        Route::get('fines/excel',      [ReportController::class, 'finesExcel'])
             ->middleware('permission:reports.export');

        // PDF reports
        Route::get('payroll/pdf',      [ReportController::class, 'payrollPdf'])
             ->middleware('permission:reports.export');
        Route::get('profit-loss/pdf',  [ReportController::class, 'profitLossPdf'])
             ->middleware('permission:reports.financial');
    });

    // ── Platform Income (owner + superadmin) ─────────────────────────────────
    Route::prefix('platform-income')->group(function () {
        Route::get('platforms',                              [PlatformIncomeController::class, 'platforms'])
             ->middleware('permission:platform_income.view');
        Route::get('stats',                                  [PlatformIncomeController::class, 'stats'])
             ->middleware('permission:platform_income.view');
        Route::get('/',                                      [PlatformIncomeController::class, 'index'])
             ->middleware('permission:platform_income.view');
        Route::post('/',                                     [PlatformIncomeController::class, 'store'])
             ->middleware('permission:platform_income.create');
        Route::get('{platformIncome}',                       [PlatformIncomeController::class, 'show'])
             ->middleware('permission:platform_income.view');
        Route::put('{platformIncome}',                       [PlatformIncomeController::class, 'update'])
             ->middleware('permission:platform_income.update');
        Route::delete('{platformIncome}',                    [PlatformIncomeController::class, 'destroy'])
             ->middleware('permission:platform_income.delete');
        Route::post('{platformIncome}/receipt',              [PlatformIncomeController::class, 'uploadReceipt'])
             ->middleware('permission:platform_income.update');
    });

    // ── Owner & SuperAdmin only ───────────────────────────────────────────────
    Route::middleware('role:owner,superadmin')->group(function () {

        // User Management
        Route::prefix('users')->group(function () {
            Route::get('/',                      [UserManagementController::class, 'index']);
            Route::post('/',                     [UserManagementController::class, 'store']);
            Route::get('{user}',                 [UserManagementController::class, 'show']);
            Route::put('{user}',                 [UserManagementController::class, 'update']);
            Route::delete('{user}',              [UserManagementController::class, 'destroy']);
            Route::patch('{user}/toggle-status', [UserManagementController::class, 'toggleStatus']);
        });

        // Permission Management
        Route::prefix('permissions')->group(function () {
            Route::get('/',                    [PermissionController::class, 'index']);
            Route::get('role/{role}',          [PermissionController::class, 'rolePermissions']);
            Route::put('role/{role}',          [PermissionController::class, 'updateRolePermissions']);
            Route::get('user/{user}',          [PermissionController::class, 'userPermissions']);
            Route::put('user/{user}',          [PermissionController::class, 'updateUserPermissions']);
            Route::delete('user/{user}/reset', [PermissionController::class, 'resetUserPermissions']);
        });

        // Profit & Loss (owner only via permission)
        Route::prefix('profit-loss')->middleware('permission:profit_loss.view')->group(function () {
            Route::get('summary',        [ProfitLossController::class, 'summary']);
            Route::get('monthly-trend',  [ProfitLossController::class, 'monthlyTrend']);
        });

        // Audit Logs
        Route::prefix('audit-logs')->group(function () {
            Route::get('/',                              [AuditLogController::class, 'index']);
            Route::get('model-types',                   [AuditLogController::class, 'modelTypes']);
            Route::get('{modelType}/{modelId}',         [AuditLogController::class, 'forModel']);
        });

    });

});
