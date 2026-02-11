<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(private DashboardService $dashboardService) {}

    public function stats(Request $request): JsonResponse
    {
        $dateFrom = $request->get('date_from', now()->subDays(30)->toDateString());
        $dateTo = $request->get('date_to', now()->toDateString());

        $stats = $this->dashboardService->getStats($dateFrom, $dateTo, $request->region);

        return response()->json([
            'success' => true,
            'data' => $stats,
            'generated_at' => now()->toIso8601String(),
        ]);
    }

    public function byAgent(Request $request): JsonResponse
    {
        $data = $this->dashboardService->getByAgent($request->region);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function map(Request $request): JsonResponse
    {
        $data = $this->dashboardService->getMapData(
            $request->date_from, $request->date_to, $request->status
        );

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }
}
