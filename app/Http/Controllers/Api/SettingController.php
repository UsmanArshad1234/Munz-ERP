<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Setting\CreateSettingRequest;
use App\Http\Requests\Setting\UpdateSettingRequest;
use App\Models\Setting;
use App\Services\SettingService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly SettingService $settingService) {}

    // GET /api/settings — all grouped by type
    public function index(): JsonResponse
    {
        return $this->success($this->settingService->getAllGrouped());
    }

    // GET /api/settings/types — list of valid types
    public function types(): JsonResponse
    {
        return $this->success($this->settingService->getTypes());
    }

    // GET /api/settings/{type} — values for one type (supports dashboard aliases)
    public function byType(string $type): JsonResponse
    {
        $resolved = Setting::TYPE_ALIASES[$type] ?? $type;

        if (!in_array($resolved, Setting::TYPES)) {
            return $this->error("Invalid setting type: {$type}", 422);
        }

        return $this->success($this->settingService->getByType($resolved));
    }

    // POST /api/settings
    public function store(CreateSettingRequest $request): JsonResponse
    {
        $setting = $this->settingService->create($request->validated());

        return $this->created($setting, 'Setting created');
    }

    // PUT /api/settings/{setting}
    public function update(UpdateSettingRequest $request, Setting $setting): JsonResponse
    {
        $updated = $this->settingService->update($setting, $request->validated());

        return $this->success($updated, 'Setting updated');
    }

    // DELETE /api/settings/{setting}
    public function destroy(Setting $setting): JsonResponse
    {
        try {
            $this->settingService->delete($setting);
            return $this->success(null, 'Setting deleted');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    // PUT /api/settings/{type}/reorder
    public function reorder(Request $request, string $type): JsonResponse
    {
        $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'integer|exists:settings,id',
        ]);

        $this->settingService->reorder($type, $request->ids);

        return $this->success(null, 'Order updated');
    }
}
