<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Referential\StoreBrandRequest;
use App\Http\Requests\Referential\StoreVehicleModelRequest;
use App\Models\Brand;
use App\Models\Color;
use App\Models\ContractType;
use App\Models\Direction;
use App\Models\FuelType;
use App\Models\InsuranceCompany;
use App\Models\Structure;
use App\Models\Transmission;
use App\Models\VehicleCategory;
use App\Models\VehicleModel;
use App\Models\VehicleStatus;
use App\Models\VehicleType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ReferentialController extends Controller
{
    // === BRANDS ===
    public function brands(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => Brand::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function storeBrand(StoreBrandRequest $request): JsonResponse
    {
        $brand = Brand::create($request->validated());

        return response()->json(['success' => true, 'data' => $brand], 201);
    }

    public function updateBrand(Request $request, string $brand): JsonResponse
    {
        $b = Brand::findOrFail($brand);
        $b->update($request->only(['name', 'is_active']));

        return response()->json(['success' => true, 'data' => $b]);
    }

    // === MODELS ===
    public function models(Request $request): JsonResponse|Response
    {
        $query = VehicleModel::with('brand')->where('is_active', true);

        if ($request->has('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }

        $data = $query->orderBy('name')->get()->map(fn($m) => [
            'id' => $m->id,
            'brand_id' => $m->brand_id,
            'brand_name' => $m->brand->name,
            'name' => $m->name,
            'category' => $m->category,
            'is_active' => $m->is_active,
        ]);

        $json = json_encode(['success' => true, 'data' => $data]);

        // Compress large payloads (listing without brand_id filter)
        if (! $request->has('brand_id') && strlen($json) > 10000) {
            $compressed = gzencode($json, 9);

            return response($compressed)
                ->header('Content-Type', 'application/json; charset=utf-8')
                ->header('Content-Encoding', 'gzip')
                ->header('Content-Length', strlen($compressed));
        }

        return response()->json(['success' => true, 'data' => $data]);
    }

    public function storeModel(StoreVehicleModelRequest $request): JsonResponse
    {
        $model = VehicleModel::create($request->validated());

        return response()->json(['success' => true, 'data' => $model->load('brand')], 201);
    }

    public function updateModel(Request $request, string $model): JsonResponse
    {
        $m = VehicleModel::findOrFail($model);
        $m->update($request->only(['name', 'category', 'brand_id', 'is_active']));

        return response()->json(['success' => true, 'data' => $m->load('brand')]);
    }

    // === STRUCTURES ===
    public function structures(): Response
    {
        $json = json_encode([
            'success' => true,
            'data' => Structure::where('is_active', true)
                ->orderBy('name')
                ->select(['id', 'code', 'name', 'sigle', 'region', 'direction_id', 'direction', 'site', 'type', 'is_active'])
                ->get(),
        ]);

        $compressed = gzencode($json, 9);

        return response($compressed)
            ->header('Content-Type', 'application/json; charset=utf-8')
            ->header('Content-Encoding', 'gzip')
            ->header('Content-Length', strlen($compressed));
    }

    public function storeStructure(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string|max:10|unique:structures,code',
            'name' => 'required|string|max:100',
            'region' => 'nullable|string|max:50',
        ]);

        $structure = Structure::create($request->only(['code', 'name', 'region']));

        return response()->json(['success' => true, 'data' => $structure], 201);
    }

    public function updateStructure(Request $request, string $structure): JsonResponse
    {
        $s = Structure::findOrFail($structure);
        $s->update($request->only(['code', 'name', 'region', 'is_active']));

        return response()->json(['success' => true, 'data' => $s]);
    }

    // === DIRECTIONS (V1.4) ===
    public function directions(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => Direction::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function storeDirection(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string|max:10|unique:directions,code',
            'name' => 'required|string|max:100',
        ]);

        $direction = Direction::create($request->only(['code', 'name']));

        return response()->json(['success' => true, 'data' => $direction], 201);
    }

    public function updateDirection(Request $request, string $direction): JsonResponse
    {
        $d = Direction::findOrFail($direction);
        $d->update($request->only(['code', 'name', 'is_active']));

        return response()->json(['success' => true, 'data' => $d]);
    }

    // === INSURANCE COMPANIES ===
    public function insuranceCompanies(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => InsuranceCompany::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function storeInsuranceCompany(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:insurance_companies,name',
        ]);

        $company = InsuranceCompany::create($request->only('name'));

        return response()->json(['success' => true, 'data' => $company], 201);
    }

    public function updateInsuranceCompany(Request $request, string $company): JsonResponse
    {
        $c = InsuranceCompany::findOrFail($company);
        $c->update($request->only(['name', 'is_active']));

        return response()->json(['success' => true, 'data' => $c]);
    }

    // === VEHICLE TYPES ===
    public function vehicleTypes(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => VehicleType::where('is_active', true)->orderBy('name')->get(),
        ]);
    }
    public function storeVehicleType(Request $request): JsonResponse
    {
        $request->validate(['name' => 'required|string|max:50|unique:vehicle_types,name']);
        $item = VehicleType::create($request->only('name'));
        return response()->json(['success' => true, 'data' => $item], 201);
    }
    public function updateVehicleType(Request $request, string $id): JsonResponse
    {
        $item = VehicleType::findOrFail($id);
        $item->update($request->only(['name', 'is_active']));
        return response()->json(['success' => true, 'data' => $item]);
    }

    // === VEHICLE CATEGORIES ===
    public function vehicleCategories(Request $request): JsonResponse
    {
        $query = VehicleCategory::where('is_active', true)->orderBy('name');
        if ($request->has('vehicle_type')) {
            $query->where('vehicle_type', $request->vehicle_type);
        }
        return response()->json(['success' => true, 'data' => $query->get()]);
    }
    public function storeVehicleCategory(Request $request): JsonResponse
    {
        $request->validate(['name' => 'required|string|max:50', 'vehicle_type' => 'nullable|string|max:50']);
        $item = VehicleCategory::create($request->only(['name', 'vehicle_type']));
        return response()->json(['success' => true, 'data' => $item], 201);
    }
    public function updateVehicleCategory(Request $request, string $id): JsonResponse
    {
        $item = VehicleCategory::findOrFail($id);
        $item->update($request->only(['name', 'vehicle_type', 'is_active']));
        return response()->json(['success' => true, 'data' => $item]);
    }

    // === FUEL TYPES ===
    public function fuelTypes(): JsonResponse
    {
        return response()->json(['success' => true, 'data' => FuelType::where('is_active', true)->orderBy('name')->get()]);
    }
    public function storeFuelType(Request $request): JsonResponse
    {
        $request->validate(['name' => 'required|string|max:50|unique:fuel_types,name']);
        return response()->json(['success' => true, 'data' => FuelType::create($request->only('name'))], 201);
    }
    public function updateFuelType(Request $request, string $id): JsonResponse
    {
        $item = FuelType::findOrFail($id);
        $item->update($request->only(['name', 'is_active']));
        return response()->json(['success' => true, 'data' => $item]);
    }

    // === TRANSMISSIONS ===
    public function transmissions(): JsonResponse
    {
        return response()->json(['success' => true, 'data' => Transmission::where('is_active', true)->orderBy('name')->get()]);
    }
    public function storeTransmission(Request $request): JsonResponse
    {
        $request->validate(['name' => 'required|string|max:50|unique:transmissions,name']);
        return response()->json(['success' => true, 'data' => Transmission::create($request->only('name'))], 201);
    }
    public function updateTransmission(Request $request, string $id): JsonResponse
    {
        $item = Transmission::findOrFail($id);
        $item->update($request->only(['name', 'is_active']));
        return response()->json(['success' => true, 'data' => $item]);
    }

    // === VEHICLE STATUSES ===
    public function vehicleStatuses(): JsonResponse
    {
        return response()->json(['success' => true, 'data' => VehicleStatus::where('is_active', true)->orderBy('name')->get()]);
    }
    public function storeVehicleStatus(Request $request): JsonResponse
    {
        $request->validate(['name' => 'required|string|max:50|unique:vehicle_statuses,name']);
        return response()->json(['success' => true, 'data' => VehicleStatus::create($request->only('name'))], 201);
    }
    public function updateVehicleStatus(Request $request, string $id): JsonResponse
    {
        $item = VehicleStatus::findOrFail($id);
        $item->update($request->only(['name', 'is_active']));
        return response()->json(['success' => true, 'data' => $item]);
    }

    // === CONTRACT TYPES ===
    public function contractTypes(): JsonResponse
    {
        return response()->json(['success' => true, 'data' => ContractType::where('is_active', true)->orderBy('name')->get()]);
    }
    public function storeContractType(Request $request): JsonResponse
    {
        $request->validate(['name' => 'required|string|max:50|unique:contract_types,name']);
        return response()->json(['success' => true, 'data' => ContractType::create($request->only('name'))], 201);
    }
    public function updateContractType(Request $request, string $id): JsonResponse
    {
        $item = ContractType::findOrFail($id);
        $item->update($request->only(['name', 'is_active']));
        return response()->json(['success' => true, 'data' => $item]);
    }

    // === COLORS ===
    public function colors(): JsonResponse
    {
        return response()->json(['success' => true, 'data' => Color::where('is_active', true)->orderBy('name')->get()]);
    }
    public function storeColor(Request $request): JsonResponse
    {
        $request->validate(['name' => 'required|string|max:50|unique:colors,name']);
        return response()->json(['success' => true, 'data' => Color::create($request->only('name'))], 201);
    }
    public function updateColor(Request $request, string $id): JsonResponse
    {
        $item = Color::findOrFail($id);
        $item->update($request->only(['name', 'is_active']));
        return response()->json(['success' => true, 'data' => $item]);
    }
}
