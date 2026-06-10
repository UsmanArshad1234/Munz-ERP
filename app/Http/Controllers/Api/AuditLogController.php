<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AuditLogService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    use ApiResponse;

    public function __construct(private AuditLogService $auditLogService) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $logs = $this->auditLogService->list($request->only([
                'model_type', 'model_id', 'action', 'user_id',
                'date_from', 'date_to', 'search', 'per_page',
            ]));

            return $this->success($logs, 'Audit logs retrieved');
        } catch (\Throwable $e) {
            return $this->error('Failed to retrieve audit logs', $e->getCode() >= 400 && $e->getCode() < 600 ? (int) $e->getCode() : 500);
        }
    }

    public function forModel(Request $request, string $modelType, int $modelId): JsonResponse
    {
        try {
            $logs = $this->auditLogService->getForModel($modelType, $modelId);
            return $this->success($logs, 'Model audit trail retrieved');
        } catch (\Throwable $e) {
            return $this->error('Failed to retrieve audit trail', $e->getCode() >= 400 && $e->getCode() < 600 ? (int) $e->getCode() : 500);
        }
    }

    public function modelTypes(): JsonResponse
    {
        try {
            $types = $this->auditLogService->getModelTypes();
            return $this->success($types, 'Model types retrieved');
        } catch (\Throwable $e) {
            return $this->error('Failed to retrieve model types', 500);
        }
    }
}
