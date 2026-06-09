<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ProfitLossService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfitLossController extends Controller
{
    use ApiResponse;

    public function __construct(private ProfitLossService $plService) {}

    public function summary(Request $request): JsonResponse
    {
        $request->validate([
            'from_date' => 'nullable|date',
            'to_date'   => 'nullable|date|after_or_equal:from_date',
            'month'     => 'nullable|integer|min:1|max:12',
            'year'      => 'nullable|integer|min:2020|max:2099',
        ]);

        $summary = $this->plService->getSummary($request->only(['from_date', 'to_date', 'month', 'year']));
        return $this->success($summary, 'Profit & Loss summary retrieved');
    }

    public function monthlyTrend(Request $request): JsonResponse
    {
        $request->validate(['year' => 'required|integer|min:2020|max:2099']);
        $trend = $this->plService->getMonthlyTrend($request->year);
        return $this->success($trend, 'Monthly P&L trend retrieved');
    }
}
