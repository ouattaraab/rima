<?php
namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\Structure;
use App\Models\User;
use App\Services\DashboardService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardService $dashboardService
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();

        // Retrieve filter parameters
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $region = $request->input('region');

        $query = Vehicle::query();

        if ($user->isSupervisorCidec()) {
            $agentIds = User::where('role', 'agent_cidec')->pluck('id');
            $query->whereIn('collected_by', $agentIds);
        }

        // Apply date filters
        if ($dateFrom) {
            $query->whereDate('collected_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('collected_at', '<=', $dateTo);
        }
        // Apply region filter
        if ($region) {
            $query->whereHas('collector', fn($q) => $q->where('region', $region));
        }

        $total = (clone $query)->count();
        $validated = (clone $query)->where('form_status', 'validated')->count();
        $rejected = (clone $query)->where('form_status', 'rejected')->count();
        $synchronized = (clone $query)->where('form_status', 'synchronized')->count();
        $draft = (clone $query)->where('form_status', 'draft')->count();

        $byType = (clone $query)->selectRaw('vehicle_type, count(*) as count')->groupBy('vehicle_type')->pluck('count', 'vehicle_type');
        $byCategory = (clone $query)->selectRaw('category, count(*) as count')->groupBy('category')->pluck('count', 'category');
        $byStatus = (clone $query)->selectRaw('form_status, count(*) as count')->groupBy('form_status')->pluck('count', 'form_status');

        $recentVehicles = (clone $query)->with('collector')->latest('collected_at')->take(10)->get();

        $topAgents = User::where('role', 'agent_cidec')
            ->withCount('vehicles')
            ->orderByDesc('vehicles_count')
            ->take(5)
            ->get();

        // Map data from DashboardService (respects role-based filtering)
        $mapAgentIds = null;
        if ($user->isSupervisorCidec()) {
            $mapAgentIds = User::where('role', 'agent_cidec')->pluck('id')->toArray();
        }
        $mapData = $this->dashboardService->getMapData($dateFrom, $dateTo, null, $mapAgentIds);

        // Regions for the filter dropdown
        $regions = Structure::where('is_active', true)
            ->whereNotNull('region')
            ->distinct()
            ->orderBy('region')
            ->pluck('region');

        return view('dashboard', compact(
            'total', 'validated', 'rejected', 'synchronized', 'draft',
            'byType', 'byCategory', 'byStatus',
            'recentVehicles', 'topAgents',
            'mapData', 'regions'
        ));
    }
}
