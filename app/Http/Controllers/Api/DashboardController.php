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
        $user = $request->user();

        $data = match (true) {
            $user->isOwner() || $user->role === 'superadmin' => $this->dashboardService->getOwnerDashboard(),
            default => $this->dashboardService->getAdminDashboard(),
        };

        return $this->success($data, 'Dashboard data retrieved');
    }

    public function alerts(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->isOwner() || $user->role === 'superadmin') {
            $data = $this->dashboardService->getOwnerDashboard()['alerts'];
        } else {
            $data = $this->dashboardService->getAdminDashboard()['alerts'];
        }

        return $this->success($data, 'Alerts retrieved');
    }
}
