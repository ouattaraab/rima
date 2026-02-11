<?php
namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\VehicleHistory;
use App\Models\User;
use App\Exports\VehiclesExport;
use App\Services\NotificationService;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VehicleController extends Controller
{
    public function index(Request $request)
    {
        $query = Vehicle::with('collector');
        $user = $request->user();

        if ($user->isSupervisorCidec()) {
            $agentIds = User::where('role', 'agent_cidec')->pluck('id');
            $query->whereIn('collected_by', $agentIds);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function($q) use ($s) {
                $q->where('registration_number', 'like', "%{$s}%")
                  ->orWhere('chassis_number', 'like', "%{$s}%")
                  ->orWhere('brand', 'like', "%{$s}%")
                  ->orWhere('model', 'like', "%{$s}%");
            });
        }

        if ($request->filled('form_status')) $query->where('form_status', $request->form_status);
        if ($request->filled('vehicle_status')) $query->where('status', $request->vehicle_status);
        if ($request->filled('category')) $query->where('category', $request->category);
        if ($request->filled('brand')) $query->where('brand', $request->brand);
        if ($request->filled('vehicle_type')) $query->where('vehicle_type', $request->vehicle_type);
        if ($request->filled('date_from')) $query->whereDate('collected_at', '>=', $request->date_from);
        if ($request->filled('date_to')) $query->whereDate('collected_at', '<=', $request->date_to);
        if ($request->filled('agent')) $query->where('collected_by', $request->agent);
        if ($request->filled('region')) {
            $query->whereHas('collector', fn($q) => $q->where('region', $request->region));
        }

        $sort = $request->get('sort', 'collected_at');
        $direction = $request->get('direction', 'desc');
        $allowed = ['registration_number','brand','vehicle_type','form_status','collected_at','status'];
        if (in_array($sort, $allowed)) $query->orderBy($sort, $direction);
        else $query->orderByDesc('collected_at');

        $vehicles = $query->paginate(20)->withQueryString();
        $brands = Vehicle::distinct()->whereNotNull('brand')->pluck('brand')->sort()->values();
        $agents = User::where('role', 'agent_cidec')->where('is_active', true)->orderBy('last_name')->get();
        $regions = \App\Models\Structure::where('is_active', true)
            ->whereNotNull('region')
            ->distinct()
            ->orderBy('region')
            ->pluck('region');

        return view('vehicles.index', compact('vehicles', 'brands', 'agents', 'regions'));
    }

    public function show(string $id)
    {
        $vehicle = Vehicle::with(['photos', 'collector', 'validator', 'histories.user'])->findOrFail($id);

        // Quality Score computation (US-022)
        $qualityData = null;
        $user = request()->user();
        if ($vehicle->form_status === 'synchronized' && in_array($user->role, ['supervisor_sodeci', 'admin_sodeci'])) {
            $missingFields = $vehicle->getMissingFields();

            $checks = [];
            // Mandatory fields filled
            $mandatoryOk = count(array_filter($missingFields, fn($f) => !str_starts_with($f, 'photo_'))) === 0;
            $checks[] = ['label' => 'Champs obligatoires remplis', 'ok' => $mandatoryOk];

            // Required photos present and files exist on disk
            $photosOk = !in_array('photo_front', $missingFields) && !in_array('photo_rear', $missingFields) && !in_array('photo_side', $missingFields);
            if ($photosOk) {
                foreach ($vehicle->photos as $photo) {
                    if (in_array($photo->photo_type, ['front', 'rear', 'side'])) {
                        $filePath = str_replace('/storage/', '', parse_url($photo->url, PHP_URL_PATH) ?? '');
                        if ($filePath && !Storage::disk('public')->exists($filePath)) {
                            $photosOk = false;
                            break;
                        }
                    }
                }
            }
            $checks[] = ['label' => 'Photos obligatoires presentes (face, arriere, laterale)', 'ok' => $photosOk];

            // Registration uniqueness
            $regDuplicate = $vehicle->registration_number
                ? Vehicle::where('registration_number', $vehicle->registration_number)->where('id', '!=', $vehicle->id)->exists()
                : false;
            $checks[] = ['label' => 'Immatriculation unique', 'ok' => !$regDuplicate];

            // Date coherence + positive numbers
            $dateOk = true;
            if ($vehicle->insurance_start_date && $vehicle->insurance_end_date && $vehicle->insurance_start_date > $vehicle->insurance_end_date) {
                $dateOk = false;
            }
            if ($vehicle->commissioning_date && $vehicle->commissioning_date->isFuture()) {
                $dateOk = false;
            }
            if ($vehicle->mileage !== null && $vehicle->mileage < 0) $dateOk = false;
            if ($vehicle->seats_count !== null && $vehicle->seats_count < 0) $dateOk = false;
            if ($vehicle->engine_displacement !== null && $vehicle->engine_displacement < 0) $dateOk = false;
            $checks[] = ['label' => 'Coherence des dates et donnees numeriques', 'ok' => $dateOk];

            // Insurance required for in-service vehicles
            $insuranceOk = true;
            if ($vehicle->status === 'En service' && !$vehicle->is_insured) $insuranceOk = false;
            $checks[] = ['label' => 'Assurance (obligatoire si en service)', 'ok' => $insuranceOk];

            $passedChecks = count(array_filter($checks, fn($c) => $c['ok']));
            $totalChecks = count($checks);
            $qualityPct = $totalChecks > 0 ? round(($passedChecks / $totalChecks) * 100) : 0;

            $qualityData = [
                'checks' => $checks,
                'missingFields' => $missingFields,
                'qualityPct' => $qualityPct,
            ];
        }

        return view('vehicles.show', compact('vehicle', 'qualityData'));
    }

    public function validateVehicle(Request $request, string $id)
    {
        $vehicle = Vehicle::findOrFail($id);
        if ($vehicle->form_status !== 'synchronized') {
            return back()->with('error', 'Seules les fiches synchronisees peuvent etre validees.');
        }

        $vehicle->update([
            'form_status' => 'validated',
            'validated_by' => $request->user()->id,
            'validated_at' => now(),
            'revision' => $vehicle->revision + 1,
        ]);

        VehicleHistory::create([
            'vehicle_id' => $vehicle->id,
            'action' => 'validated',
            'changed_by' => $request->user()->id,
            'comment' => $request->input('comment'),
        ]);

        // Notify the collecting agent
        (new NotificationService())->notifyValidation($vehicle);

        return back()->with('success', 'Fiche vehicule validee avec succes.');
    }

    public function reject(Request $request, string $id)
    {
        $request->validate([
            'rejection_reason' => 'required|in:photo_issue,registration_error,data_inconsistency,missing_information,other',
            'rejection_comment' => 'required|string|min:20|max:1000',
        ], [
            'rejection_comment.required' => 'Le commentaire est obligatoire.',
            'rejection_comment.min' => 'Le commentaire doit contenir au moins 20 caracteres.',
        ]);

        $vehicle = Vehicle::findOrFail($id);
        if ($vehicle->form_status !== 'synchronized') {
            return back()->with('error', 'Seules les fiches synchronisees peuvent etre rejetees.');
        }

        $vehicle->update([
            'form_status' => 'rejected',
            'validated_by' => $request->user()->id,
            'validated_at' => now(),
            'rejection_reason' => $request->rejection_reason,
            'rejection_comment' => $request->rejection_comment,
            'revision' => $vehicle->revision + 1,
        ]);

        VehicleHistory::create([
            'vehicle_id' => $vehicle->id,
            'action' => 'rejected',
            'changed_by' => $request->user()->id,
            'comment' => "Motif: {$request->rejection_reason}. {$request->rejection_comment}",
        ]);

        // Notify the collecting agent
        (new NotificationService())->notifyRejection($vehicle, $request->rejection_reason, $request->rejection_comment);

        return back()->with('success', 'Fiche vehicule rejetee.');
    }

    public function financial(string $id)
    {
        $vehicle = Vehicle::findOrFail($id);
        return view('vehicles.financial', compact('vehicle'));
    }

    public function updateFinancial(Request $request, string $id)
    {
        $request->validate([
            'financing_mode' => 'required|in:Leasing,Direct',
            'bank_name' => 'nullable|string|max:50|required_if:financing_mode,Leasing',
            'contract_number' => 'nullable|string|max:50|required_if:financing_mode,Leasing',
            'withdrawal_start_date' => 'nullable|date|required_if:financing_mode,Leasing',
            'withdrawal_end_date' => 'nullable|date|after_or_equal:withdrawal_start_date|required_if:financing_mode,Leasing',
            'contract_start_date' => 'nullable|date',
            'provision_date' => 'required|date',
        ]);

        $vehicle = Vehicle::findOrFail($id);
        $oldValues = $vehicle->only(['financing_mode','bank_name','contract_number','withdrawal_start_date','withdrawal_end_date','contract_start_date','provision_date']);

        $data = $request->only(['financing_mode','bank_name','contract_number','withdrawal_start_date','withdrawal_end_date','contract_start_date','provision_date']);
        if ($request->financing_mode === 'Direct') {
            $data['bank_name'] = null;
            $data['contract_number'] = null;
            $data['withdrawal_start_date'] = null;
            $data['withdrawal_end_date'] = null;
        }

        $vehicle->update($data);
        $vehicle->increment('revision');

        VehicleHistory::create([
            'vehicle_id' => $vehicle->id,
            'action' => 'updated',
            'changed_by' => $request->user()->id,
            'old_values' => $oldValues,
            'new_values' => $data,
            'comment' => 'Mise a jour des donnees financieres',
        ]);

        return redirect()->route('vehicles.show', $vehicle->id)->with('success', 'Donnees financieres mises a jour.');
    }

    public function export(Request $request)
    {
        $filters = $request->only(['form_status', 'brand', 'vehicle_type', 'category', 'vehicle_status', 'date_from', 'date_to']);
        $filename = 'RIMA_EXPORT_' . now()->format('Ymd_His') . '.xlsx';

        return Excel::download(new VehiclesExport($filters), $filename);
    }

    public function edit(string $id)
    {
        $vehicle = Vehicle::findOrFail($id);
        return view('vehicles.edit', compact('vehicle'));
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'modification_reason' => 'required|string|min:5|max:500',
            'vehicle_type' => 'nullable|string',
            'category' => 'nullable|string',
            'brand' => 'nullable|string|max:100',
            'model' => 'nullable|string|max:100',
            'version' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:30',
            'commissioning_date' => 'nullable|date',
            'contract_type' => 'nullable|string',
            'registration_number' => 'nullable|string|max:10',
            'temporary_registration' => 'nullable|string|max:10',
            'chassis_number' => 'nullable|string|max:30',
            'chassis_readable' => 'nullable|boolean',
            'fuel_type' => 'nullable|string',
            'transmission' => 'nullable|string',
            'engine_displacement' => 'nullable|integer',
            'seats_count' => 'nullable|integer|min:1',
            'load_capacity' => 'nullable|integer',
            'mileage' => 'nullable|integer|min:0',
            'status' => 'nullable|string',
            'structure_ci' => 'nullable|string|max:50',
            'has_roll_bars' => 'nullable|boolean',
            'special_equipment' => 'nullable|string|max:100',
            'technical_inspection_date' => 'nullable|date',
            'is_insured' => 'nullable|boolean',
            'insurance_company' => 'nullable|string|max:50',
            'policy_number' => 'nullable|string|max:30',
            'coverage_type' => 'nullable|string',
            'insurance_start_date' => 'nullable|date',
            'insurance_end_date' => 'nullable|date',
            'user_direction' => 'nullable|string|max:100',
            'user_matricule' => 'nullable|string|max:7',
            'user_driver_license' => 'nullable|string|max:50',
        ], [
            'modification_reason.required' => 'Le motif de modification est obligatoire.',
            'modification_reason.min' => 'Le motif doit contenir au moins 5 caracteres.',
        ]);

        $vehicle = Vehicle::findOrFail($id);

        $editableFields = [
            'vehicle_type', 'category', 'brand', 'model', 'version', 'color',
            'commissioning_date', 'contract_type',
            'registration_number', 'temporary_registration', 'chassis_number', 'chassis_readable',
            'fuel_type', 'transmission', 'engine_displacement', 'seats_count',
            'load_capacity', 'mileage', 'status', 'structure_ci',
            'has_roll_bars', 'special_equipment', 'technical_inspection_date',
            'is_insured', 'insurance_company', 'policy_number', 'coverage_type',
            'insurance_start_date', 'insurance_end_date',
            'user_direction', 'user_matricule', 'user_driver_license',
        ];

        $oldValues = $vehicle->only($editableFields);
        $newData = $request->only($editableFields);

        $vehicle->update($newData);
        $vehicle->increment('revision');

        VehicleHistory::create([
            'vehicle_id' => $vehicle->id,
            'action' => 'updated',
            'changed_by' => $request->user()->id,
            'old_values' => $oldValues,
            'new_values' => $newData,
            'comment' => 'Modification admin: ' . $request->modification_reason,
        ]);

        return redirect()->route('vehicles.show', $vehicle->id)->with('success', 'Fiche vehicule modifiee avec succes.');
    }
}
