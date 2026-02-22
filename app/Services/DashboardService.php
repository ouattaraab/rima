<?php

namespace App\Services;

use App\Models\Vehicle;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function getStats(?string $dateFrom = null, ?string $dateTo = null, ?string $region = null): array
    {
        $query = Vehicle::query();

        if ($dateFrom) $query->whereDate('collected_at', '>=', $dateFrom);
        if ($dateTo) $query->whereDate('collected_at', '<=', $dateTo);
        if ($region) $query->whereHas('collector', fn($q) => $q->where('region', $region));

        $total = (clone $query)->count();
        $validated = (clone $query)->where('form_status', 'validated')->count();
        $rejected = (clone $query)->where('form_status', 'rejected')->count();
        $pending = (clone $query)->where('form_status', 'synchronized')->count();

        $byType = (clone $query)->select('vehicle_type', DB::raw('count(*) as total'))
            ->groupBy('vehicle_type')->pluck('total', 'vehicle_type');

        $byCategory = (clone $query)->where('vehicle_type', 'Auto')
            ->select('category', DB::raw('count(*) as total'))
            ->groupBy('category')->pluck('total', 'category');

        $byStatus = (clone $query)->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')->pluck('total', 'status');

        return [
            'total_vehicles_collected' => $total,
            'pending_validation' => $pending,
            'validated' => $validated,
            'rejected' => $rejected,
            'completion_rate_percentage' => $total > 0 ? round(($validated / $total) * 100, 1) : 0,
            'rejection_rate_percentage' => $total > 0 ? round(($rejected / $total) * 100, 1) : 0,
            'by_vehicle_type' => $byType,
            'by_category' => $byCategory,
            'by_status' => $byStatus,
        ];
    }

    public function getByAgent(?string $region = null): array
    {
        $query = User::where('role', 'agent_cidec')
            ->withCount([
                'vehicles',
                'vehicles as validated_count' => fn($q) => $q->where('form_status', 'validated'),
                'vehicles as rejected_count' => fn($q) => $q->where('form_status', 'rejected'),
            ]);

        if ($region) $query->where('region', $region);

        return $query->get()->map(function ($agent) {
            return [
                'agent' => [
                    'id' => $agent->id,
                    'username' => $agent->username,
                    'first_name' => $agent->first_name,
                    'last_name' => $agent->last_name,
                ],
                'vehicles_collected' => $agent->vehicles_count,
                'validated' => $agent->validated_count,
                'rejected' => $agent->rejected_count,
                'rejection_rate' => $agent->vehicles_count > 0
                    ? round(($agent->rejected_count / $agent->vehicles_count) * 100, 1) : 0,
                'last_collection' => $agent->vehicles()->latest('collected_at')->value('collected_at'),
            ];
        })->toArray();
    }

    public function getMapData(?string $dateFrom = null, ?string $dateTo = null, ?string $status = null, ?array $agentIds = null): array
    {
        $query = Vehicle::whereNotNull('gps_latitude')->whereNotNull('gps_longitude');

        if ($agentIds) $query->whereIn('collected_by', $agentIds);
        if ($dateFrom) $query->whereDate('collected_at', '>=', $dateFrom);
        if ($dateTo) $query->whereDate('collected_at', '<=', $dateTo);
        if ($status) $query->where('form_status', $status);

        $vehicles = $query->select('id', 'registration_number', 'brand', 'model', 'form_status', 'collected_at', 'gps_latitude', 'gps_longitude')->get();

        return [
            'type' => 'FeatureCollection',
            'features' => $vehicles->map(fn($v) => [
                'type' => 'Feature',
                'geometry' => [
                    'type' => 'Point',
                    'coordinates' => [(float)$v->gps_longitude, (float)$v->gps_latitude],
                ],
                'properties' => [
                    'vehicle_id' => $v->id,
                    'registration_number' => $v->registration_number,
                    'brand' => $v->brand,
                    'model' => $v->model,
                    'form_status' => $v->form_status,
                    'collected_at' => $v->collected_at,
                ],
            ])->toArray(),
        ];
    }
}
