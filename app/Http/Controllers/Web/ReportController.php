<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\Structure;
use App\Models\Direction;
use App\Models\User;
use App\Services\DashboardService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function regional(Request $request)
    {
        $structures = Structure::where('is_active', true)->orderBy('code')->get();
        $directions = Direction::where('is_active', true)->orderBy('name')->get();
        $selectedStructures = $request->input('structures', []);
        if (!is_array($selectedStructures)) $selectedStructures = [$selectedStructures];
        $selectedDirections = $request->input('directions', []);
        if (!is_array($selectedDirections)) $selectedDirections = [$selectedDirections];
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        // If directions are selected, also filter by their structures
        $effectiveStructures = $selectedStructures;
        if (!empty($selectedDirections)) {
            $dirStructureCodes = Structure::whereIn('direction_id', $selectedDirections)->pluck('code')->toArray();
            if (!empty($effectiveStructures)) {
                $effectiveStructures = array_intersect($effectiveStructures, $dirStructureCodes);
            } else {
                $effectiveStructures = $dirStructureCodes;
            }
        }

        $query = Vehicle::query();
        if (!empty($effectiveStructures)) {
            $query->whereIn('structure_ci', $effectiveStructures);
        }
        if ($dateFrom) {
            $query->whereDate('collected_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('collected_at', '<=', $dateTo);
        }

        $total = (clone $query)->count();
        $validated = (clone $query)->where('form_status', 'validated')->count();
        $rejected = (clone $query)->where('form_status', 'rejected')->count();
        $synchronized = (clone $query)->where('form_status', 'synchronized')->count();

        $completionRate = $total > 0 ? round(($validated / $total) * 100, 1) : 0;
        $rejectionRate = $total > 0 ? round(($rejected / $total) * 100, 1) : 0;

        // Per-direction breakdown (for chart)
        $byDirectionQuery = Vehicle::selectRaw("
                COALESCE(directions.code, 'N/A') as direction_code,
                COALESCE(directions.name, 'Sans direction') as direction_name,
                count(*) as total")
            ->selectRaw("SUM(CASE WHEN vehicles.form_status = 'validated' THEN 1 ELSE 0 END) as validated")
            ->selectRaw("SUM(CASE WHEN vehicles.form_status = 'rejected' THEN 1 ELSE 0 END) as rejected")
            ->selectRaw("SUM(CASE WHEN vehicles.form_status = 'synchronized' THEN 1 ELSE 0 END) as synchronized")
            ->leftJoin('structures', 'vehicles.structure_ci', '=', 'structures.code')
            ->leftJoin('directions', 'structures.direction_id', '=', 'directions.id')
            ->whereNotNull('vehicles.structure_ci')
            ->where('vehicles.structure_ci', '!=', '');

        if (!empty($effectiveStructures)) {
            $byDirectionQuery->whereIn('vehicles.structure_ci', $effectiveStructures);
        }
        if ($dateFrom) $byDirectionQuery->whereDate('vehicles.collected_at', '>=', $dateFrom);
        if ($dateTo) $byDirectionQuery->whereDate('vehicles.collected_at', '<=', $dateTo);

        $byDirection = $byDirectionQuery
            ->groupByRaw("COALESCE(directions.code, 'N/A'), COALESCE(directions.name, 'Sans direction')")
            ->orderByDesc('total')
            ->get();

        // Per-structure breakdown (for detail table)
        $byRegionQuery = Vehicle::selectRaw("vehicles.structure_ci, structures.name as structure_name, structures.sigle as structure_sigle, count(*) as total")
            ->selectRaw("SUM(CASE WHEN vehicles.form_status = 'validated' THEN 1 ELSE 0 END) as validated")
            ->selectRaw("SUM(CASE WHEN vehicles.form_status = 'rejected' THEN 1 ELSE 0 END) as rejected")
            ->selectRaw("SUM(CASE WHEN vehicles.form_status = 'synchronized' THEN 1 ELSE 0 END) as synchronized")
            ->leftJoin('structures', 'vehicles.structure_ci', '=', 'structures.code')
            ->whereNotNull('vehicles.structure_ci')
            ->where('vehicles.structure_ci', '!=', '');

        if (!empty($effectiveStructures)) {
            $byRegionQuery->whereIn('vehicles.structure_ci', $effectiveStructures);
        }
        if ($dateFrom) $byRegionQuery->whereDate('vehicles.collected_at', '>=', $dateFrom);
        if ($dateTo) $byRegionQuery->whereDate('vehicles.collected_at', '<=', $dateTo);

        $byRegion = $byRegionQuery->groupBy('vehicles.structure_ci', 'structures.name', 'structures.sigle')
            ->orderByDesc('total')
            ->get();

        return view('reports.regional', compact(
            'structures', 'directions', 'selectedStructures', 'selectedDirections', 'dateFrom', 'dateTo',
            'total', 'validated', 'rejected', 'synchronized',
            'completionRate', 'rejectionRate', 'byDirection', 'byRegion'
        ));
    }

    public function compliance(Request $request)
    {
        $today = now();

        // Vehicles with expired insurance
        $expiredInsurance = Vehicle::where('is_insured', true)
            ->whereNotNull('insurance_end_date')
            ->whereDate('insurance_end_date', '<', $today)
            ->where('form_status', 'validated')
            ->with('collector')
            ->orderBy('insurance_end_date')
            ->get();

        // Vehicles with technical inspection > 1 year
        $expiredInspection = Vehicle::whereNotNull('technical_inspection_date')
            ->whereDate('technical_inspection_date', '<', $today->copy()->subYear())
            ->where('form_status', 'validated')
            ->with('collector')
            ->orderBy('technical_inspection_date')
            ->get();

        // Vehicles without definitive registration
        $noRegistration = Vehicle::where(function ($q) {
                $q->whereNull('registration_number')->orWhere('registration_number', '');
            })
            ->where('form_status', 'validated')
            ->with('collector')
            ->orderByDesc('collected_at')
            ->get();

        // In-service vehicles without insurance
        $noInsurance = Vehicle::where('status', 'En service')
            ->where(function ($q) {
                $q->where('is_insured', false)->orWhereNull('is_insured');
            })
            ->where('form_status', 'validated')
            ->with('collector')
            ->orderByDesc('collected_at')
            ->get();

        return view('reports.compliance', compact(
            'expiredInsurance', 'expiredInspection', 'noRegistration', 'noInsurance'
        ));
    }

    public function regionalExport(Request $request)
    {
        return Excel::download(
            new \App\Exports\RegionalReportExport(
                $request->input('structures', []),
                $request->input('date_from'),
                $request->input('date_to')
            ),
            'PRIMA_STRUCTURE_' . now()->format('Ymd_His') . '.xlsx'
        );
    }

    public function complianceExport(Request $request)
    {
        return Excel::download(
            new \App\Exports\ComplianceExport(),
            'PRIMA_CONFORMITE_' . now()->format('Ymd_His') . '.xlsx'
        );
    }

    public function agents(Request $request)
    {
        // Build query with optional search
        $query = \App\Models\User::where('role', 'agent_cidec');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%");
            });
        }

        $agentUsers = $query->orderBy('last_name')->orderBy('first_name')->get();

        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $agents = $agentUsers->map(function ($agent) use ($dateFrom, $dateTo) {
            $vehicles = Vehicle::where('collected_by', $agent->id);
            if ($dateFrom) $vehicles->whereDate('collected_at', '>=', $dateFrom);
            if ($dateTo) $vehicles->whereDate('collected_at', '<=', $dateTo);

            $total = (clone $vehicles)->count();
            $validated = (clone $vehicles)->where('form_status', 'validated')->count();
            $rejected = (clone $vehicles)->where('form_status', 'rejected')->count();
            $synchronized = (clone $vehicles)->where('form_status', 'synchronized')->count();
            $lastCollection = (clone $vehicles)->max('collected_at');

            return (object) [
                'id' => $agent->id,
                'full_name' => $agent->full_name,
                'username' => $agent->username,
                'region' => $agent->region,
                'total' => $total,
                'validated' => $validated,
                'rejected' => $rejected,
                'synchronized' => $synchronized,
                'rejection_rate' => $total > 0 ? round(($rejected / $total) * 100, 1) : 0,
                'last_collection' => $lastCollection ? \Carbon\Carbon::parse($lastCollection)->format('d/m/Y H:i') : null,
            ];
        });

        // Sort
        $sort = $request->input('sort', 'total');
        $direction = $request->input('direction', 'desc');
        $agents = $direction === 'asc'
            ? $agents->sortBy($sort)->values()
            : $agents->sortByDesc($sort)->values();

        return view('reports.agents', compact('agents', 'dateFrom', 'dateTo'));
    }

    public function agentShow(Request $request, string $user)
    {
        $agent = User::where('role', 'agent_cidec')->findOrFail($user);
        $dashboardService = app(DashboardService::class);

        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        // KPIs
        $kpiQuery = Vehicle::where('collected_by', $agent->id);
        if ($dateFrom) $kpiQuery->whereDate('collected_at', '>=', $dateFrom);
        if ($dateTo) $kpiQuery->whereDate('collected_at', '<=', $dateTo);

        $total = (clone $kpiQuery)->count();
        $validated = (clone $kpiQuery)->where('form_status', 'validated')->count();
        $rejected = (clone $kpiQuery)->where('form_status', 'rejected')->count();
        $synchronized = (clone $kpiQuery)->where('form_status', 'synchronized')->count();
        $rejectionRate = $total > 0 ? round(($rejected / $total) * 100, 1) : 0;

        // Map data (reuse DashboardService)
        $mapData = $dashboardService->getMapData($dateFrom, $dateTo, null, [$agent->id]);

        // Vehicles table — paginated, searchable, sortable
        $vQuery = Vehicle::where('collected_by', $agent->id);
        if ($dateFrom) $vQuery->whereDate('collected_at', '>=', $dateFrom);
        if ($dateTo) $vQuery->whereDate('collected_at', '<=', $dateTo);

        if ($request->filled('search')) {
            $s = $request->search;
            $vQuery->where(function ($q) use ($s) {
                $q->where('registration_number', 'like', "%{$s}%")
                  ->orWhere('chassis_number', 'like', "%{$s}%")
                  ->orWhere('brand', 'like', "%{$s}%");
            });
        }
        if ($request->filled('form_status')) {
            $vQuery->where('form_status', $request->form_status);
        }

        $sort = $request->input('sort', 'collected_at');
        $dir = $request->input('direction', 'desc');
        $allowedSorts = ['registration_number', 'brand', 'vehicle_type', 'form_status', 'status', 'collected_at'];
        if (!in_array($sort, $allowedSorts)) $sort = 'collected_at';

        $vehicles = $vQuery->orderBy($sort, $dir)->paginate(20)->withQueryString();

        return view('reports.agent-show', compact(
            'agent', 'total', 'validated', 'rejected', 'synchronized', 'rejectionRate',
            'mapData', 'vehicles', 'dateFrom', 'dateTo'
        ));
    }
}
