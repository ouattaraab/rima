<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vehicle\StoreVehicleRequest;
use App\Http\Requests\Vehicle\UpdateVehicleRequest;
use App\Models\Vehicle;
use App\Models\VehicleDriver;
use App\Services\VehicleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    public function __construct(private VehicleService $vehicleService) {}

    public function index(Request $request): JsonResponse
    {
        $query = Vehicle::where('collected_by', $request->user()->id);

        if ($request->has('status')) {
            $query->where('form_status', $request->status);
        }

        $sort = $request->get('sort', '-collected_at');
        $direction = str_starts_with($sort, '-') ? 'desc' : 'asc';
        $column = ltrim($sort, '-');
        $query->orderBy($column, $direction);

        $limit = min((int) $request->get('limit', 20), 100);
        $vehicles = $query->paginate($limit);

        return response()->json([
            'success' => true,
            'data' => $vehicles->map(fn($v) => [
                'id' => $v->id,
                'vehicle_type' => $v->vehicle_type,
                'category' => $v->category,
                'brand' => $v->brand,
                'model' => $v->model,
                'registration_number' => $v->registration_number,
                'form_status' => $v->form_status,
                'completion_percentage' => $v->completion_percentage,
                'collected_at' => $v->collected_at,
                'updated_at' => $v->updated_at,
            ]),
            'pagination' => [
                'current_page' => $vehicles->currentPage(),
                'total_pages' => $vehicles->lastPage(),
                'total_items' => $vehicles->total(),
                'items_per_page' => $vehicles->perPage(),
                'has_next' => $vehicles->hasMorePages(),
                'has_prev' => $vehicles->currentPage() > 1,
            ],
        ]);
    }

    public function store(StoreVehicleRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $driversData = $validated['drivers'] ?? null;
        unset($validated['drivers']);

        // Ignore drivers when vehicle is marked "Non affecte"
        if (!empty($validated['driver_not_assigned'])) {
            $driversData = null;
        }

        // ── Find-or-create: prevent duplicates from mobile sync ──
        $existing = $this->findExistingDraft($request->user()->id, $validated);

        if ($existing) {
            // Update the existing draft instead of creating a duplicate
            $existing->update($validated);
            $this->syncDrivers($existing, $driversData, $validated);
            $existing->refresh();

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $existing->id,
                    'vehicle_type' => $existing->vehicle_type,
                    'category' => $existing->category,
                    'brand' => $existing->brand,
                    'model' => $existing->model,
                    'registration_number' => $existing->registration_number,
                    'form_status' => $existing->form_status,
                    'completion_percentage' => $existing->completion_percentage,
                    'missing_fields' => $existing->getMissingFields(),
                    'collected_by' => $existing->collected_by,
                    'collected_at' => $existing->collected_at,
                    'created_at' => $existing->created_at,
                    'version' => $existing->revision,
                ],
                'message' => 'Fiche vehicule existante mise a jour (doublon evite)',
            ], 200);
        }

        $vehicle = Vehicle::create([
            ...$validated,
            'collected_by' => $request->user()->id,
            'collected_at' => now(),
            'form_status' => 'draft',
        ]);

        // Process drivers
        $this->syncDrivers($vehicle, $driversData, $validated);

        $this->vehicleService->recordHistory($vehicle, 'created', $request->user()->id);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $vehicle->id,
                'vehicle_type' => $vehicle->vehicle_type,
                'category' => $vehicle->category,
                'brand' => $vehicle->brand,
                'model' => $vehicle->model,
                'registration_number' => $vehicle->registration_number,
                'form_status' => $vehicle->form_status,
                'completion_percentage' => $vehicle->completion_percentage,
                'missing_fields' => $vehicle->getMissingFields(),
                'collected_by' => $vehicle->collected_by,
                'collected_at' => $vehicle->collected_at,
                'created_at' => $vehicle->created_at,
                'version' => $vehicle->revision,
            ],
            'message' => 'Fiche vehicule creee avec succes',
        ], 201);
    }

    public function show(Request $request, string $vehicle): JsonResponse
    {
        $v = Vehicle::with(['photos', 'drivers', 'collector', 'validator'])->findOrFail($vehicle);

        if ($request->user()->isAgentCidec() && $v->collected_by !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'error' => ['code' => 'INSUFFICIENT_PERMISSIONS', 'message' => 'Acces non autorise a cette fiche.'],
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $v,
        ]);
    }

    public function update(UpdateVehicleRequest $request, string $vehicle): JsonResponse
    {
        $v = Vehicle::findOrFail($vehicle);

        if ($request->user()->isAgentCidec() && $v->collected_by !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'error' => ['code' => 'INSUFFICIENT_PERMISSIONS', 'message' => 'Acces non autorise.'],
            ], 403);
        }

        if (!$v->isEditable()) {
            return response()->json([
                'success' => false,
                'error' => ['code' => 'MODIFICATION_NOT_ALLOWED', 'message' => 'Seules les fiches en brouillon ou rejetees peuvent etre modifiees.'],
            ], 403);
        }

        $validated = $request->validated();
        $driversData = $validated['drivers'] ?? null;
        unset($validated['drivers']);

        // Ignore drivers when vehicle is marked "Non affecte"
        if (!empty($validated['driver_not_assigned'])) {
            $driversData = null;
        }

        // Reset rejected vehicles back to draft when updated
        if ($v->form_status === 'rejected') {
            $validated['form_status'] = 'draft';
        }

        $oldValues = $v->only(array_keys($validated));
        $v->update($validated);

        // Sync drivers if provided
        if ($driversData !== null) {
            $this->syncDrivers($v, $driversData, $validated);
        }

        $v->increment('revision');

        $this->vehicleService->recordHistory($v, 'updated', $request->user()->id, $oldValues, $request->validated());

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $v->id,
                'form_status' => $v->form_status,
                'completion_percentage' => $v->completion_percentage,
                'updated_at' => $v->updated_at,
                'version' => $v->revision,
            ],
            'message' => 'Fiche vehicule mise a jour avec succes',
        ]);
    }

    public function destroy(Request $request, string $vehicle): JsonResponse
    {
        $v = Vehicle::findOrFail($vehicle);

        if ($request->user()->isAgentCidec() && $v->collected_by !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'error' => ['code' => 'INSUFFICIENT_PERMISSIONS', 'message' => 'Acces non autorise.'],
            ], 403);
        }

        if (!$v->isDraft()) {
            return response()->json([
                'success' => false,
                'error' => ['code' => 'DELETION_NOT_ALLOWED', 'message' => 'Seules les fiches en brouillon peuvent etre supprimees.'],
            ], 403);
        }

        $v->delete();

        return response()->json(null, 204);
    }

    public function sync(Request $request, string $vehicle): JsonResponse
    {
        $v = Vehicle::findOrFail($vehicle);

        if ($request->user()->isAgentCidec() && $v->collected_by !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'error' => ['code' => 'INSUFFICIENT_PERMISSIONS', 'message' => 'Acces non autorise.'],
            ], 403);
        }

        if (!$v->isEditable()) {
            return response()->json([
                'success' => false,
                'error' => ['code' => 'MODIFICATION_NOT_ALLOWED', 'message' => 'Seules les fiches en brouillon ou rejetees peuvent etre synchronisees.'],
            ], 403);
        }

        $v = $this->vehicleService->syncVehicle($v, $request->user()->id);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $v->id,
                'form_status' => 'synchronized',
                'synchronized_at' => $v->updated_at,
            ],
            'message' => 'Fiche synchronisee avec succes',
        ]);
    }

    public function batchSync(Request $request): JsonResponse
    {
        $request->validate(['vehicle_ids' => 'required|array|min:1', 'vehicle_ids.*' => 'uuid']);

        $results = [];
        $succeeded = 0;
        $failed = 0;

        foreach ($request->vehicle_ids as $vehicleId) {
            try {
                $v = Vehicle::where('id', $vehicleId)->where('collected_by', $request->user()->id)->firstOrFail();
                $this->vehicleService->syncVehicle($v, $request->user()->id);
                $results[] = ['vehicle_id' => $vehicleId, 'status' => 'success', 'synchronized_at' => now()];
                $succeeded++;
            } catch (\Exception $e) {
                $results[] = ['vehicle_id' => $vehicleId, 'status' => 'failed', 'error' => $e->getMessage()];
                $failed++;
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'total' => count($request->vehicle_ids),
                'succeeded' => $succeeded,
                'failed' => $failed,
                'results' => $results,
            ],
            'message' => "Synchronisation terminee: {$succeeded}/" . count($request->vehicle_ids) . " fiches synchronisees",
        ]);
    }

    /**
     * Find an existing vehicle (POST /vehicles/find-existing).
     * Used by mobile to check if a vehicle already exists before creating.
     */
    public function findExisting(Request $request): JsonResponse
    {
        $request->validate([
            'registration_number' => 'nullable|string',
            'temporary_registration' => 'nullable|string',
            'chassis_number' => 'nullable|string',
        ]);

        $existing = $this->findExistingDraft(
            $request->user()->id,
            $request->only(['registration_number', 'temporary_registration', 'chassis_number'])
        );

        if ($existing) {
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $existing->id,
                    'registration_number' => $existing->registration_number,
                    'form_status' => $existing->form_status,
                ],
                'message' => 'Vehicule existant trouve',
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => null,
            'message' => 'Aucun vehicule existant trouve',
        ]);
    }

    /**
     * Find an existing draft/rejected vehicle with matching identifiers for the same agent.
     * Returns the vehicle if found, null otherwise.
     */
    private function findExistingDraft(string $userId, array $data): ?Vehicle
    {
        $regNum = $data['registration_number'] ?? null;
        $tempReg = $data['temporary_registration'] ?? null;
        $chassis = $data['chassis_number'] ?? null;

        // Need at least one identifier to search
        if (!$regNum && !$tempReg && !$chassis) {
            return null;
        }

        return Vehicle::where('collected_by', $userId)
            ->whereIn('form_status', ['draft', 'rejected'])
            ->where(function ($q) use ($regNum, $tempReg, $chassis) {
                if ($regNum) {
                    $q->orWhere('registration_number', $regNum);
                }
                if ($tempReg) {
                    $q->orWhere('temporary_registration', $tempReg);
                }
                if ($chassis) {
                    $q->orWhere('chassis_number', $chassis);
                }
            })
            ->latest('updated_at')
            ->first();
    }

    /**
     * Sync drivers for a vehicle (delete all + re-insert).
     * Handles both new format (drivers array) and old format (user_* fields).
     */
    private function syncDrivers(Vehicle $vehicle, ?array $driversData, array $validated): void
    {
        if ($driversData !== null) {
            // New format: drivers array
            $vehicle->drivers()->delete();
            foreach ($driversData as $index => $d) {
                $vehicle->drivers()->create([
                    'direction' => $d['direction'],
                    'matricule' => $d['matricule'],
                    'driver_license' => $d['driver_license'],
                    'is_primary' => $d['is_primary'] ?? ($index === 0),
                    'position' => $index,
                ]);
            }
        } elseif (!empty($validated['user_matricule'])) {
            // Old format: single-driver fields (backward compat)
            $vehicle->drivers()->delete();
            $vehicle->drivers()->create([
                'direction' => $validated['user_direction'] ?? '',
                'matricule' => $validated['user_matricule'],
                'driver_license' => $validated['user_driver_license'] ?? '',
                'is_primary' => true,
                'position' => 0,
            ]);
        }
    }
}
