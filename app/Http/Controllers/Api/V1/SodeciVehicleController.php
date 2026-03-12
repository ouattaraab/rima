<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vehicle\RejectVehicleRequest;
use App\Http\Requests\Vehicle\SodeciUpdateVehicleRequest;
use App\Http\Requests\Vehicle\UpdateFinancialRequest;
use App\Http\Requests\Vehicle\ValidateVehicleRequest;
use App\Models\Vehicle;
use App\Services\VehicleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class SodeciVehicleController extends Controller
{
    public function __construct(private VehicleService $vehicleService) {}

    public function index(Request $request): JsonResponse
    {
        $query = Vehicle::with('collector');

        // Supervisor CIDEC only sees CIDEC agents' vehicles
        if ($request->user()->isSupervisorCidec()) {
            $agentIds = \App\Models\User::where('role', 'agent_cidec')->pluck('id');
            $query->whereIn('collected_by', $agentIds);
        }

        // Filters
        if ($request->has('status')) $query->where('form_status', $request->status);
        if ($request->has('vehicle_status')) $query->where('status', $request->vehicle_status);
        if ($request->has('brand')) $query->where('brand', 'like', "%{$request->brand}%");
        if ($request->has('model')) $query->where('model', 'like', "%{$request->model}%");
        if ($request->has('category')) $query->where('category', $request->category);
        if ($request->has('collected_by')) $query->where('collected_by', $request->collected_by);
        if ($request->has('registration_number')) $query->where('registration_number', 'like', "%{$request->registration_number}%");
        if ($request->has('chassis_number')) $query->where('chassis_number', 'like', "%{$request->chassis_number}%");
        if ($request->has('date_from')) $query->whereDate('collected_at', '>=', $request->date_from);
        if ($request->has('date_to')) $query->whereDate('collected_at', '<=', $request->date_to);
        if ($request->has('region')) {
            $query->whereHas('collector', fn($q) => $q->where('region', $request->region));
        }
        if ($request->boolean('insurance_expired')) {
            $query->where('is_insured', true)->where('insurance_end_date', '<', now());
        }

        $sort = $request->get('sort', '-collected_at');
        $direction = str_starts_with($sort, '-') ? 'desc' : 'asc';
        $column = ltrim($sort, '-');
        $query->orderBy($column, $direction);

        $limit = min((int) $request->get('limit', 50), 100);
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
                'chassis_number' => $v->chassis_number,
                'status' => $v->status,
                'form_status' => $v->form_status,
                'collected_by' => $v->collector ? [
                    'id' => $v->collector->id,
                    'username' => $v->collector->username,
                    'first_name' => $v->collector->first_name,
                    'last_name' => $v->collector->last_name,
                ] : null,
                'collected_at' => $v->collected_at,
                'completion_percentage' => $v->completion_percentage,
            ]),
            'pagination' => [
                'current_page' => $vehicles->currentPage(),
                'total_pages' => $vehicles->lastPage(),
                'total_items' => $vehicles->total(),
                'items_per_page' => $vehicles->perPage(),
            ],
        ]);
    }

    public function show(string $vehicle): JsonResponse
    {
        $v = Vehicle::with(['photos', 'collector', 'validator', 'histories.user', 'drivers'])->findOrFail($vehicle);

        return response()->json([
            'success' => true,
            'data' => $v,
        ]);
    }

    public function update(SodeciUpdateVehicleRequest $request, string $vehicle): JsonResponse
    {
        $v = Vehicle::findOrFail($vehicle);
        $oldValues = $v->only(array_keys($request->fields_to_update));

        $v->update($request->fields_to_update);
        $v->increment('revision');

        $this->vehicleService->recordHistory(
            $v, 'updated', $request->user()->id,
            $oldValues, $request->fields_to_update,
            $request->modification_reason
        );

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $v->id,
                'updated_at' => $v->fresh()->updated_at,
                'version' => $v->revision,
            ],
            'message' => 'Vehicule modifie avec succes. Historique enregistre.',
        ]);
    }

    public function validateVehicle(ValidateVehicleRequest $request, string $vehicle): JsonResponse
    {
        $v = Vehicle::findOrFail($vehicle);

        if (!$v->isSynchronized()) {
            return response()->json([
                'success' => false,
                'error' => ['code' => 'INVALID_STATUS', 'message' => 'Seules les fiches synchronisees peuvent etre validees.'],
            ], 422);
        }

        // CDC: Verification coherence avant validation
        $coherenceErrors = $v->getCoherenceErrors();
        if (!empty($coherenceErrors)) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'COHERENCE_ERROR',
                    'message' => 'Des incoherences ont ete detectees dans la fiche.',
                    'details' => ['errors' => $coherenceErrors],
                ],
            ], 422);
        }

        $v = $this->vehicleService->validateVehicle($v, $request->user()->id, $request->comment);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $v->id,
                'form_status' => 'validated',
                'validated_by' => [
                    'id' => $request->user()->id,
                    'username' => $request->user()->username,
                ],
                'validated_at' => $v->validated_at,
            ],
            'message' => 'Fiche validee avec succes',
        ]);
    }

    public function reject(RejectVehicleRequest $request, string $vehicle): JsonResponse
    {
        $v = Vehicle::findOrFail($vehicle);

        if (!$v->isSynchronized()) {
            return response()->json([
                'success' => false,
                'error' => ['code' => 'INVALID_STATUS', 'message' => 'Seules les fiches synchronisees peuvent etre rejetees.'],
            ], 422);
        }

        $v = $this->vehicleService->rejectVehicle(
            $v, $request->user()->id,
            $request->rejection_reason, $request->rejection_comment
        );

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $v->id,
                'form_status' => 'rejected',
                'rejection_reason' => $v->rejection_reason,
                'rejection_comment' => $v->rejection_comment,
                'rejected_by' => [
                    'id' => $request->user()->id,
                    'username' => $request->user()->username,
                ],
                'rejected_at' => $v->validated_at,
            ],
            'message' => 'Fiche rejetee.',
        ]);
    }

    public function updateFinancial(UpdateFinancialRequest $request, string $vehicle): JsonResponse
    {
        $v = Vehicle::findOrFail($vehicle);

        $financialFields = [
            'financing_mode', 'bank_name', 'contract_number',
            'withdrawal_start_date', 'withdrawal_end_date',
            'contract_start_date', 'provision_date',
            'code_immo_dfc', 'code_immo_dbcg', 'code_equipement',
        ];

        $oldValues = $v->only($financialFields);
        $newValues = $request->only($financialFields);

        // Si mode Direct, nettoyer les champs Leasing
        if ($request->financing_mode === 'Direct') {
            $newValues['bank_name'] = null;
            $newValues['contract_number'] = null;
            $newValues['withdrawal_start_date'] = null;
            $newValues['withdrawal_end_date'] = null;
        }

        $v->update($newValues);
        $v->increment('revision');

        $this->vehicleService->recordHistory(
            $v, 'updated', $request->user()->id,
            $oldValues, $newValues,
            'Mise a jour des donnees financieres'
        );

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $v->id,
                'financing_mode' => $v->financing_mode,
                'bank_name' => $v->bank_name,
                'contract_number' => $v->contract_number,
                'withdrawal_start_date' => $v->withdrawal_start_date,
                'withdrawal_end_date' => $v->withdrawal_end_date,
                'contract_start_date' => $v->contract_start_date,
                'provision_date' => $v->provision_date,
                'updated_at' => $v->fresh()->updated_at,
                'version' => $v->revision,
            ],
            'message' => 'Donnees financieres mises a jour avec succes.',
        ]);
    }

    public function export(Request $request)
    {
        $query = Vehicle::with(['collector', 'drivers']);

        if ($request->has('status')) $query->where('form_status', $request->status);
        if ($request->has('brand')) $query->where('brand', $request->brand);
        if ($request->has('date_from')) $query->whereDate('collected_at', '>=', $request->date_from);
        if ($request->has('date_to')) $query->whereDate('collected_at', '<=', $request->date_to);

        $vehicles = $query->get();

        // Pre-load structure lookup for direction display
        $structureLookup = \App\Models\Structure::where('is_active', true)
            ->pluck('sigle', 'code')
            ->map(fn ($sigle, $code) => $code . ' - ' . ($sigle ?? $code))
            ->toArray();

        $data = $vehicles->map(function ($v) use ($structureLookup) {
            $primary = $v->drivers->firstWhere('is_primary', true) ?? $v->drivers->first();
            $directionDisplay = $primary
                ? ($structureLookup[$primary->direction] ?? $primary->direction)
                : ($structureLookup[$v->user_direction] ?? $v->user_direction);

            return [
                'Type' => $v->vehicle_type,
                'Categorie' => $v->category,
                'Marque' => $v->brand,
                'Modele' => $v->model,
                'Immatriculation' => $v->registration_number,
                'Chassis' => $v->chassis_number,
                'Statut vehicule' => $v->status,
                'Statut fiche' => $v->form_status,
                'Carburant' => $v->fuel_type,
                'Kilometrage' => $v->mileage,
                'Assure' => $v->is_insured ? 'Oui' : 'Non',
                'Assureur' => $v->insurance_company,
                // Section conducteurs (multi-driver)
                'Direction (principal)' => $directionDisplay,
                'Matricule (principal)' => $primary?->matricule ?? $v->user_matricule,
                'Permis (principal)' => $primary?->driver_license ?? $v->user_driver_license,
                'Nb conducteurs' => $v->drivers->count(),
                'Autres conducteurs' => $v->drivers->where('is_primary', '!=', true)->pluck('matricule')->implode(', '),
                // Section 5.9 - V1.4
                'Mode financement' => $v->financing_mode,
                'Banque' => $v->bank_name,
                'N° contrat' => $v->contract_number,
                'Debut prelevement' => $v->withdrawal_start_date?->format('d/m/Y'),
                'Fin prelevement' => $v->withdrawal_end_date?->format('d/m/Y'),
                'Debut contrat' => $v->contract_start_date?->format('d/m/Y'),
                'Mise a disposition' => $v->provision_date?->format('d/m/Y'),
                'Collecte par' => $v->collector?->full_name,
                'Date collecte' => $v->collected_at?->format('d/m/Y H:i'),
                'Latitude' => $v->gps_latitude,
                'Longitude' => $v->gps_longitude,
            ];
        })->toArray();

        $filename = 'PRIMA_EXPORT_' . now()->format('Ymd_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM UTF-8
            if (!empty($data)) {
                fputcsv($file, array_keys($data[0]), ';');
                foreach ($data as $row) {
                    fputcsv($file, $row, ';');
                }
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
