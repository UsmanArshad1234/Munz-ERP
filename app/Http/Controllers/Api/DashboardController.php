<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    use ApiResponse;

    public function __construct(private DashboardService $dashboardService) {}

    public function overview(Request $request): JsonResponse
    {
        [$month, $year] = $this->parseMonth($request->query('month'));
        $filters        = $this->parseFilters($request);
        $user           = $request->user();

        $data = ($user->isOwner() || $user->role === 'superadmin')
            ? $this->dashboardService->getOwnerDashboard($month, $year, $filters)
            : $this->dashboardService->getAdminDashboard($month, $year, $filters);

        return $this->success($data, 'Dashboard data retrieved');
    }

    public function status(Request $request): JsonResponse
    {
        [$month, $year] = $this->parseMonth($request->query('month'));
        $filters        = $this->parseFilters($request);

        $data = $this->dashboardService->getStatusChecks($month, $year, $filters);

        return $this->success($data, 'Status checks retrieved');
    }

    public function alerts(Request $request): JsonResponse
    {
        [$month, $year] = $this->parseMonth($request->query('month'));
        $filters        = $this->parseFilters($request);
        $user           = $request->user();

        $data = ($user->isOwner() || $user->role === 'superadmin')
            ? $this->dashboardService->getOwnerDashboard($month, $year, $filters)['alerts']
            : $this->dashboardService->getAdminDashboard($month, $year, $filters)['expiry_alerts'];

        return $this->success($data, 'Alerts retrieved');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Parse ?month=YYYY-MM → [month, year]. Defaults to current month.
     */
    private function parseMonth(?string $param): array
    {
        if ($param && preg_match('/^(\d{4})-(\d{2})$/', $param, $m)) {
            $year  = (int) $m[1];
            $month = (int) $m[2];
            if ($month >= 1 && $month <= 12) {
                return [$month, $year];
            }
        }
        return [(int) now()->month, (int) now()->year];
    }

    /**
     * Parse optional filter query params. Null / empty strings are excluded.
     */
    private function parseFilters(Request $request): array
    {
        return array_filter([
            'emirate'         => $request->query('emirate'),
            'zone'            => $request->query('zone'),
            'platform'        => $request->query('platform'),
            'employee_status' => $request->query('employee_status'),
            'bike_status'     => $request->query('bike_status'),
            'employee_id'     => $request->query('employee_id') ? (int) $request->query('employee_id') : null,
        ]);
    }
}
