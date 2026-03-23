<?php
namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\InsuranceCompany;
use App\Models\Structure;
use App\Models\Vehicle;
use App\Models\VehicleHistory;
use App\Models\VehicleModel;
use App\Models\User;
use App\Exports\VehiclesExport;
use App\Exports\VehiclePdfExport;
use App\Services\NotificationService;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\VehicleCategory;

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
            $s = trim($request->search);
            $query->where(function($q) use ($s) {
                $q->where('registration_number', 'like', "%{$s}%")
                  ->orWhere('temporary_registration', 'like', "%{$s}%")
                  ->orWhere('chassis_number', 'like', "%{$s}%")
                  ->orWhere('structure_ci', 'like', "%{$s}%");
            });
        }

        // form_status: always respect selected chips (search + filters are in same form now)
        $formStatuses = $request->input('form_status', ['synchronized', 'validated']);
        if (!is_array($formStatuses)) $formStatuses = [$formStatuses];
        $formStatuses = array_filter($formStatuses);
        if (!empty($formStatuses) && !in_array('all', $formStatuses)) {
            $query->whereIn('form_status', $formStatuses);
        }
        if ($request->filled('vehicle_status')) $query->where('status', $request->vehicle_status);
        if ($request->filled('category')) $query->where('category', $request->category);
        if ($request->filled('brand')) $query->where('brand', $request->brand);
        if ($request->filled('vehicle_type')) $query->where('vehicle_type', $request->vehicle_type);
        if ($request->filled('date_from')) $query->whereDate('collected_at', '>=', $request->date_from);
        if ($request->filled('date_to')) $query->whereDate('collected_at', '<=', $request->date_to);
        if ($request->filled('agent')) $query->where('collected_by', $request->agent);
        if ($request->filled('structures')) {
            $structureCodes = is_array($request->structures) ? $request->structures : [$request->structures];
            $query->whereIn('structure_ci', $structureCodes);
        }

        $sort = $request->get('sort', 'collected_at');
        $direction = $request->get('direction', 'desc');
        $allowed = ['registration_number','brand','category','vehicle_type','form_status','collected_at','status','collected_by'];
        if (in_array($sort, $allowed)) $query->orderBy($sort, $direction);
        else $query->orderByDesc('collected_at');

        $vehicles = $query->paginate(20)->withQueryString();
        $brands = Brand::where('is_active', true)->orderBy('name')->get();
        $vehicleModels = VehicleModel::where('is_active', true)->with('brand')->orderBy('name')->get();
        $categories = \App\Models\VehicleCategory::where('is_active', true)->orderBy('name')->get();
        $vehicleTypes = \App\Models\VehicleType::where('is_active', true)->orderBy('name')->get();
        $vehicleStatuses = \App\Models\VehicleStatus::where('is_active', true)->orderBy('name')->get();
        $agents = User::where('role', 'agent_cidec')->where('is_active', true)->orderBy('last_name')->get();
        $structures = \App\Models\Structure::where('is_active', true)->orderBy('code')->get();

        return view('vehicles.index', compact('vehicles', 'brands', 'vehicleModels', 'categories', 'vehicleTypes', 'vehicleStatuses', 'agents', 'structures'));
    }

    public function show(string $id)
    {
        $vehicle = Vehicle::with(['photos', 'collector', 'validator', 'histories.user', 'drivers'])->findOrFail($id);

        // Quality Score computation (US-022)
        $qualityData = null;
        $user = request()->user();
        if (in_array($vehicle->form_status, ['synchronized', 'validated']) && in_array($user->role, ['supervisor_sodeci', 'admin_sodeci'])) {
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

            // Financial data completeness (dates du contrat uniquement pour "Sous contrat")
            $financialFields = ['financing_mode', 'code_immo_dfc', 'code_immo_dbcg'];
            if ($vehicle->contract_type === 'Sous contrat') {
                $financialFields[] = 'contract_start_date';
                $financialFields[] = 'provision_date';
            }
            $financialFilled = 0;
            foreach ($financialFields as $ff) {
                if (!empty($vehicle->$ff)) $financialFilled++;
            }
            $financialOk = $financialFilled === count($financialFields);
            $checks[] = ['label' => 'Données financières complètes (' . $financialFilled . '/' . count($financialFields) . ')', 'ok' => $financialOk];

            $passedChecks = count(array_filter($checks, fn($c) => $c['ok']));
            $totalChecks = count($checks);
            $qualityPct = $totalChecks > 0 ? round(($passedChecks / $totalChecks) * 100) : 0;

            $qualityData = [
                'checks' => $checks,
                'missingFields' => $missingFields,
                'qualityPct' => $qualityPct,
            ];
        }

        // Resolve structure_ci code to display label
        $structureLabel = null;
        if ($vehicle->structure_ci) {
            $struct = Structure::where('code', $vehicle->structure_ci)->first();
            $structureLabel = $struct ? $struct->display_label : $vehicle->structure_ci;
        }

        // Resolve direction codes for drivers to display labels
        $structureLookup = Structure::where('is_active', true)
            ->get()
            ->keyBy('code')
            ->map(fn ($s) => $s->display_label)
            ->toArray();

        $driversWithLabels = $vehicle->drivers->map(function ($driver) use ($structureLookup) {
            $driver->direction_label = $structureLookup[$driver->direction] ?? $driver->direction;
            return $driver;
        });

        // Legacy fallback for user_direction (backward compat)
        $userDirectionLabel = null;
        if ($vehicle->user_direction) {
            $userDirectionLabel = $structureLookup[$vehicle->user_direction] ?? $vehicle->user_direction;
        }

        return view('vehicles.show', compact('vehicle', 'qualityData', 'structureLabel', 'userDirectionLabel', 'driversWithLabels'));
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
        $user = request()->user();

        // Role-based section visibility
        $showDbcgSection = $user->isAdminSodeci() || $user->isFinanceDbcg();
        $showDfcSection  = $user->isAdminSodeci() || $user->isFinanceDfc();

        // Load banks for DFC leasing select
        $banks = \App\Models\Bank::where('is_active', true)->orderBy('name')->get();

        return view('vehicles.financial', compact('vehicle', 'showDbcgSection', 'showDfcSection', 'banks'));
    }

    public function updateFinancial(Request $request, string $id)
    {
        $vehicle = Vehicle::findOrFail($id);
        $user = $request->user();
        $isSousContrat = $vehicle->contract_type === 'Sous contrat';

        // ── Build rules & allowed fields based on role ──
        $rules = [];
        $messages = [];
        $allowedFields = [];

        // DFC fields (financing_mode, leasing info, code_immo_dfc)
        if ($user->isAdminSodeci() || $user->isFinanceDfc()) {
            $rules['code_immo_dfc'] = ['nullable', 'string', 'size:7', 'regex:/^[0-9]{7}$/'];
            $messages['code_immo_dfc.size'] = 'Le code IMMO DFC doit contenir exactement 7 chiffres.';
            $messages['code_immo_dfc.regex'] = 'Le code IMMO DFC doit contenir uniquement des chiffres.';
            $allowedFields[] = 'code_immo_dfc';
            $rules['financing_mode'] = 'required|in:Leasing,Direct';
            $rules['bank_name'] = 'nullable|string|max:50|required_if:financing_mode,Leasing';
            $rules['contract_number'] = 'nullable|string|max:50|required_if:financing_mode,Leasing';
            $rules['withdrawal_start_date'] = 'nullable|date|required_if:financing_mode,Leasing';
            $rules['withdrawal_end_date'] = 'nullable|date|after_or_equal:withdrawal_start_date|required_if:financing_mode,Leasing';

            $messages['financing_mode.required'] = 'Le mode de financement est obligatoire.';
            $messages['financing_mode.in'] = 'Le mode de financement doit etre Leasing ou Direct.';
            $messages['bank_name.required_if'] = 'Le nom de la banque est obligatoire pour le leasing.';
            $messages['bank_name.max'] = 'Le nom de la banque ne doit pas depasser 50 caracteres.';
            $messages['contract_number.required_if'] = 'Le numero de contrat est obligatoire pour le leasing.';
            $messages['contract_number.max'] = 'Le numero de contrat ne doit pas depasser 50 caracteres.';
            $messages['withdrawal_start_date.required_if'] = 'La date de debut de prelevement est obligatoire pour le leasing.';
            $messages['withdrawal_end_date.required_if'] = 'La date de fin de prelevement est obligatoire pour le leasing.';
            $messages['withdrawal_end_date.after_or_equal'] = 'La date de fin de prelevement doit etre posterieure ou egale a la date de debut.';

            array_push($allowedFields, 'financing_mode', 'bank_name', 'contract_number', 'withdrawal_start_date', 'withdrawal_end_date');
        }

        // DBCG fields (code_equipement, code_immo_dbcg, contract dates)
        if ($user->isAdminSodeci() || $user->isFinanceDbcg()) {
            $rules['code_immo_dbcg'] = ['nullable', 'string', 'size:7', 'regex:/^[0-9]{7}$/'];
            $messages['code_immo_dbcg.size'] = 'Le code IMMO DBCG doit contenir exactement 7 chiffres.';
            $messages['code_immo_dbcg.regex'] = 'Le code IMMO DBCG doit contenir uniquement des chiffres.';
            $rules['code_equipement'] = ['nullable', 'string', 'size:4', 'regex:/^[0-9]{4}$/'];
            $rules['contract_start_date'] = 'nullable|date';
            $rules['provision_date'] = $isSousContrat ? 'required|date' : 'nullable|date';

            $messages['code_equipement.size'] = 'Le code equipement doit contenir exactement 4 chiffres.';
            $messages['code_equipement.regex'] = 'Le code equipement doit contenir uniquement des chiffres.';
            $messages['provision_date.required'] = 'La date de mise a disposition est obligatoire.';
            $messages['provision_date.date'] = 'La date de mise a disposition doit etre une date valide.';
            $messages['contract_start_date.date'] = 'La date de debut de contrat doit etre une date valide.';

            array_push($allowedFields, 'code_immo_dbcg', 'code_equipement', 'contract_start_date', 'provision_date');
        }

        $request->validate($rules, $messages);

        // ── Capture old values & build update data (only allowed fields) ──
        $oldValues = $vehicle->only($allowedFields);
        $data = $request->only($allowedFields);

        // DFC: Clear leasing fields when mode is Direct
        if (in_array('financing_mode', $allowedFields) && ($request->financing_mode ?? null) === 'Direct') {
            $data['bank_name'] = null;
            $data['contract_number'] = null;
            $data['withdrawal_start_date'] = null;
            $data['withdrawal_end_date'] = null;
        }

        // DBCG: Clear contract dates if vehicle is not "Sous contrat"
        if (in_array('contract_start_date', $allowedFields) && !$isSousContrat) {
            $data['contract_start_date'] = null;
            $data['provision_date'] = null;
        }

        $vehicle->update($data);
        $vehicle->increment('revision');

        // Audit trail with role info
        $roleLabel = $user->role_label ?? $user->role;
        VehicleHistory::create([
            'vehicle_id' => $vehicle->id,
            'action' => 'updated',
            'changed_by' => $user->id,
            'old_values' => $oldValues,
            'new_values' => $data,
            'comment' => "Mise a jour des donnees financieres ({$roleLabel})",
        ]);

        return redirect()->route('vehicles.show', $vehicle->id)->with('success', 'Donnees financieres mises a jour.');
    }

    public function export(Request $request)
    {
        $filters = $request->only(['form_status', 'brand', 'vehicle_type', 'category', 'vehicle_status', 'date_from', 'date_to', 'structures']);
        $filename = 'PRIMA_EXPORT_' . now()->format('Ymd_His') . '.xlsx';

        return Excel::download(new VehiclesExport($filters), $filename);
    }

    public function motosTemplate()
    {
        return Excel::download(new \App\Exports\MotosTemplateExport(), 'PRIMA_TEMPLATE_MOTOS.xlsx');
    }

    public function importMotos(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        $import = new \App\Imports\MotosImport(auth()->id());

        try {
            Excel::import($import, $request->file('file'));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Moto import failed', ['error' => $e->getMessage()]);

            return redirect()->route('vehicles.index')
                ->with('error', "Erreur lors de l'import: " . Str::limit($e->getMessage(), 200));
        }

        $message = "{$import->imported} moto(s) importee(s).";

        if (!empty($import->duplicates)) {
            $message .= " {$import->skipped} doublon(s) rejete(s): " . implode(' | ', array_slice($import->duplicates, 0, 10));
        }

        if (!empty($import->errors)) {
            $message .= ' ' . count($import->errors) . ' erreur(s): ' . implode(' | ', array_slice($import->errors, 0, 5));
        }

        // Audit log for import traceability
        \App\Models\AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'import_motos',
            'entity_type' => 'Vehicle',
            'entity_id' => null,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'source' => 'web',
            'request_body' => [
                'filename' => $request->file('file')->getClientOriginalName(),
                'imported' => $import->imported,
                'duplicates_rejected' => $import->skipped,
                'errors' => count($import->errors),
                'duplicate_details' => array_slice($import->duplicates, 0, 50),
                'error_details' => array_slice($import->errors, 0, 20),
            ],
            'response_status' => 200,
        ]);

        return redirect()->route('vehicles.index')
            ->with('success', $message);
    }

    /**
     * Export a single vehicle as PDF (CDC Chapter 8 - Export PDF).
     */
    public function downloadPdf(string $id)
    {
        $vehicle = Vehicle::findOrFail($id);
        return VehiclePdfExport::generate($vehicle);
    }

    /**
     * Export filtered vehicle list as PDF (CDC Chapter 8 - Export PDF).
     */
    public function exportPdf(Request $request)
    {
        $query = Vehicle::with('collector');

        if ($request->filled('form_status')) {
            $statuses = (array) $request->form_status;
            $statuses = array_filter($statuses);
            if (!empty($statuses) && !in_array('all', $statuses)) {
                $query->whereIn('form_status', $statuses);
            }
        }
        if ($request->filled('vehicle_type')) $query->where('vehicle_type', $request->vehicle_type);
        if ($request->filled('brand')) $query->where('brand', $request->brand);
        if ($request->filled('category')) $query->where('category', $request->category);
        if ($request->filled('date_from')) $query->whereDate('collected_at', '>=', $request->date_from);
        if ($request->filled('date_to')) $query->whereDate('collected_at', '<=', $request->date_to);
        if ($request->filled('structures')) {
            $codes = is_array($request->structures) ? $request->structures : [$request->structures];
            $query->whereIn('structure_ci', $codes);
        }

        $vehicles = $query->orderByDesc('collected_at')->get();
        $filters = $request->only(['form_status', 'vehicle_type', 'brand', 'category', 'date_from', 'date_to', 'structures']);

        return VehiclePdfExport::generateList($vehicles, $filters);
    }

    public function edit(string $id)
    {
        $vehicle = Vehicle::with('drivers')->findOrFail($id);
        $brands = Brand::where('is_active', true)->orderBy('name')->get();
        $vehicleModels = VehicleModel::with('brand')->where('is_active', true)->orderBy('name')->get();
        $categories = VehicleCategory::where('is_active', true)->orderBy('name')->get();
        $structures = Structure::where('is_active', true)->orderBy('code')->get();
        $insuranceCompanies = InsuranceCompany::where('is_active', true)->orderBy('name')->get();

        return view('vehicles.edit', compact(
            'vehicle', 'brands', 'vehicleModels', 'categories', 'structures', 'insuranceCompanies'
        ));
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'modification_reason' => 'required|string|min:5|max:500',
            'vehicle_type' => 'nullable|string|in:Auto,Moto',
            'category' => ['nullable', 'string', Rule::in(VehicleCategory::where('is_active', true)->pluck('name')->toArray())],
            'brand' => 'nullable|string|max:100',
            'model' => 'nullable|string|max:100',
            'version' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:30|in:Blanc,Noir,Gris,Bleu,Rouge,Vert,Jaune,Beige,Marron,Autre',
            'commissioning_date' => 'nullable|date|before_or_equal:today',
            'contract_type' => 'nullable|string|in:Sous contrat,Flotte',
            'registration_number' => 'nullable|string|max:10|regex:/^[A-Z0-9\s\-]+$/i',
            'temporary_registration' => 'nullable|string|max:10|regex:/^[A-Z0-9\s\-]+$/i',
            'chassis_number' => 'nullable|string|max:30|regex:/^[A-Z0-9]+$/i',
            'chassis_readable' => 'nullable|boolean',
            'fuel_type' => 'nullable|string|in:Essence,Gasoil,Hybride,Electrique',
            'transmission' => 'nullable|string|in:Automatique,Manuelle',
            'engine_displacement' => 'nullable|integer|min:50|max:99999',
            'seats_count' => ['nullable', 'integer', 'min:1', 'max:' . ($request->input('vehicle_type') === 'Moto' ? 2 : ($request->input('category') === 'Camion' ? 10 : 7))],
            'load_capacity' => 'nullable|integer|min:1|max:99999',
            'mileage' => 'nullable|integer|min:1|max:9999999',
            'status' => 'nullable|string|in:En service,En reparation,Reforme,Cede',
            'structure_ci' => 'nullable|string|max:50',
            'has_roll_bars' => 'nullable|boolean',
            'special_equipment' => 'nullable|string|max:100',
            'technical_inspection_date' => 'nullable|date|before_or_equal:today',
            'is_insured' => 'nullable|boolean',
            'insurance_company' => 'nullable|string|max:50',
            'policy_number' => 'nullable|string|max:30',
            'insurance_start_date' => 'nullable|date',
            'insurance_end_date' => 'nullable|date|after:insurance_start_date',
            // Multi-driver validation
            'drivers' => 'nullable|array|min:1',
            'drivers.*.direction' => 'required|string|max:100',
            'drivers.*.matricule' => 'nullable|string|size:7|regex:/^(?=.*[A-Z])[A-Z0-9]{7}$/i',
            'drivers.*.driver_license' => 'required|string|max:50',
            'drivers.*.is_primary' => 'sometimes|boolean',
        ], [
            'modification_reason.required' => 'Le motif de modification est obligatoire.',
            'modification_reason.min' => 'Le motif doit contenir au moins 5 caracteres.',
            'commissioning_date.before_or_equal' => 'La date de mise en circulation ne peut pas etre dans le futur.',
            'technical_inspection_date.before_or_equal' => 'La date de controle technique ne peut pas etre dans le futur.',
            'seats_count.min' => 'Le nombre de places doit etre superieur a 0.',
            'mileage.min' => 'Le kilometrage doit etre strictement positif.',
            'load_capacity.min' => 'La charge utile doit etre superieure a 0.',
            'insurance_end_date.after' => 'La date de fin d\'assurance doit etre posterieure a la date de debut.',
            'drivers.*.matricule.size' => 'Le matricule du conducteur doit comporter exactement 7 caracteres.',
            'drivers.*.matricule.regex' => 'Le matricule du conducteur doit etre compose uniquement de caracteres alphanumeriques.',
            'drivers.*.direction.required' => 'La direction est obligatoire pour chaque conducteur.',
            'drivers.*.driver_license.required' => 'Le permis est obligatoire pour chaque conducteur.',
            'engine_displacement.min' => 'La cylindree doit etre au moins 50 cm3.',
            'registration_number.regex' => 'L\'immatriculation ne doit contenir que des lettres, chiffres, espaces et tirets.',
            'chassis_number.regex' => 'Le numero de chassis ne doit contenir que des lettres et chiffres.',
        ]);

        $vehicle = Vehicle::findOrFail($id);

        $errors = [];
        $category = $request->input('category', $vehicle->category);
        $vehicleType = $request->input('vehicle_type', $vehicle->vehicle_type);
        $status = $request->input('status', $vehicle->status);

        // CDC ID-05: Version only for Berline
        if (!empty($request->version) && $category !== 'Berline') {
            $errors['version'] = 'La version ne concerne que les Berlines.';
        }
        // CDC ST-03: Special equipment only for Camion
        if (!empty($request->special_equipment) && $category !== 'Camion') {
            $errors['special_equipment'] = 'Les equipements speciaux ne concernent que les Camions.';
        }
        // CDC: Transmission prohibited for Moto
        if (!empty($request->transmission) && $vehicleType === 'Moto') {
            $errors['transmission'] = 'La transmission n\'est pas applicable pour les Motos.';
        }
        // CDC: Insurance required for "En service"
        if ($status === 'En service' && $request->has('is_insured') && !$request->boolean('is_insured')) {
            $errors['is_insured'] = 'L\'assurance est obligatoire pour les vehicules en service.';
        }
        // CDC: Structure required for "En service" / "En reparation"
        if (in_array($status, ['En service', 'En reparation']) && empty($request->input('structure_ci', $vehicle->structure_ci))) {
            $errors['structure_ci'] = 'La structure est obligatoire pour les vehicules en service ou en reparation.';
        }

        if (!empty($errors)) {
            return back()->withErrors($errors)->withInput();
        }

        $editableFields = [
            'vehicle_type', 'category', 'brand', 'model', 'version', 'color',
            'commissioning_date', 'contract_type',
            'registration_number', 'temporary_registration', 'chassis_number', 'chassis_readable',
            'fuel_type', 'transmission', 'engine_displacement', 'seats_count',
            'load_capacity', 'mileage', 'status', 'structure_ci',
            'has_roll_bars', 'special_equipment', 'technical_inspection_date',
            'is_insured', 'insurance_company', 'policy_number',
            'insurance_start_date', 'insurance_end_date',
        ];

        $oldValues = $vehicle->only($editableFields);
        $newData = $request->only($editableFields);

        $vehicle->update($newData);
        $vehicle->increment('revision');

        // Sync drivers (delete + re-insert)
        if ($request->has('drivers')) {
            $vehicle->drivers()->delete();
            $driversInput = $request->input('drivers', []);
            $hasPrimary = collect($driversInput)->contains(fn ($d) => !empty($d['is_primary']));

            foreach ($driversInput as $i => $driverData) {
                \App\Models\VehicleDriver::create([
                    'vehicle_id' => $vehicle->id,
                    'direction' => $driverData['direction'],
                    'matricule' => strtoupper($driverData['matricule']),
                    'driver_license' => $driverData['driver_license'],
                    'is_primary' => !empty($driverData['is_primary']) || (!$hasPrimary && $i === 0),
                    'position' => $i,
                ]);
            }

            // Also update legacy fields from primary driver for backward compat
            $primary = $vehicle->drivers()->where('is_primary', true)->first();
            if ($primary) {
                $vehicle->update([
                    'user_direction' => $primary->direction,
                    'user_matricule' => $primary->matricule,
                    'user_driver_license' => $primary->driver_license,
                ]);
            }
        }

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
