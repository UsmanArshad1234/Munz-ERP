<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Maintenance\CreateMaintenanceRequest;
use App\Http\Requests\Maintenance\UpdateMaintenanceRequest;
use App\Models\Maintenance;
use App\Services\MaintenanceService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MaintenanceController extends Controller
{
    use ApiResponse;

    public function __construct(private MaintenanceService $maintenanceService) {}

    public function index(Request $request): JsonResponse
    {
        return $this->success($this->maintenanceService->list($request->all()), 'Maintenance records retrieved');
    }

    public function store(CreateMaintenanceRequest $request): JsonResponse
    {
        try {
            $record = $this->maintenanceService->create($request->validated(), $request->user()->id);
            return $this->created($record->load('motorbike:id,bike_id,plate_number'), 'Maintenance recorded successfully');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function show(Maintenance $maintenance): JsonResponse
    {
        return $this->success($this->maintenanceService->show($maintenance), 'Maintenance record retrieved');
    }

    public function update(UpdateMaintenanceRequest $request, Maintenance $maintenance): JsonResponse
    {
        try {
            $updated = $this->maintenanceService->update($maintenance, $request->validated());
            return $this->success($updated, 'Maintenance record updated');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function destroy(Maintenance $maintenance): JsonResponse
    {
        try {
            $this->maintenanceService->destroy($maintenance);
            return $this->success(null, 'Maintenance record deleted');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function uploadReceipt(Request $request, Maintenance $maintenance): JsonResponse
    {
        $request->validate(['receipt' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120']);

        try {
            $path = $this->maintenanceService->uploadReceipt($maintenance, $request->file('receipt'));
            return $this->success(['receipt_path' => $path], 'Receipt uploaded successfully');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function upcoming(Request $request): JsonResponse
    {
        $days    = $request->integer('days', 30);
        $records = $this->maintenanceService->getUpcomingMaintenance($days);
        return $this->success($records, "Upcoming maintenance in next {$days} days");
    }

    public function stats(): JsonResponse
    {
        return $this->success($this->maintenanceService->getStats(), 'Maintenance statistics retrieved');
    }
}
