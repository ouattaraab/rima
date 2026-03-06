<?php
namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\VehicleModel;
use App\Models\Structure;
use App\Models\InsuranceCompany;
use App\Models\Direction;
use App\Models\VehicleType;
use App\Models\VehicleCategory;
use App\Models\FuelType;
use App\Models\Transmission;
use App\Models\VehicleStatus;
use App\Models\ContractType;
use App\Models\Color;
use App\Exports\BrandsExport;
use App\Exports\VehicleModelsExport;
use App\Exports\StructuresExport;
use App\Exports\InsuranceCompaniesExport;
use App\Exports\DirectionsExport;
use App\Imports\BrandsImport;
use App\Imports\VehicleModelsImport;
use App\Imports\StructuresImport;
use App\Imports\InsuranceCompaniesImport;
use App\Imports\DirectionsImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReferentialController extends Controller
{
    // ===================== BRANDS =====================
    public function brands() { return view('referentials.brands', ['items' => Brand::orderBy('name')->paginate(20)]); }
    public function storeBrand(Request $request) {
        $request->validate(['name' => 'required|string|max:50|unique:brands,name']);
        Brand::create($request->only('name'));
        return back()->with('success', 'Marque ajoutee.');
    }
    public function updateBrand(Request $request, string $id) {
        Brand::findOrFail($id)->update($request->only(['name', 'is_active']));
        return back()->with('success', 'Marque mise a jour.');
    }
    public function exportBrands() {
        return Excel::download(new BrandsExport(), 'PRIMA_MARQUES_' . now()->format('Ymd_His') . '.xlsx');
    }
    public function importBrands(Request $request) {
        $request->validate(['file' => 'required|file|mimes:xlsx,xls,csv|max:2048']);
        Excel::import(new BrandsImport(), $request->file('file'));
        return back()->with('success', 'Import des marques effectue avec succes.');
    }

    // ===================== MODELS =====================
    public function models() { return view('referentials.models', ['items' => VehicleModel::with('brand')->orderBy('name')->paginate(20), 'brands' => Brand::where('is_active', true)->orderBy('name')->get(), 'categories' => VehicleCategory::where('is_active', true)->orderBy('name')->get()]); }
    public function storeModel(Request $request) {
        $request->validate(['brand_id' => 'required|uuid|exists:brands,id', 'name' => 'required|string|max:100', 'category' => 'nullable|string|max:20']);
        VehicleModel::create($request->only(['brand_id', 'name', 'category']));
        return back()->with('success', 'Modele ajoute.');
    }
    public function updateModel(Request $request, string $id) {
        VehicleModel::findOrFail($id)->update($request->only(['name', 'brand_id', 'category', 'is_active']));
        return back()->with('success', 'Modele mis a jour.');
    }
    public function exportModels() {
        return Excel::download(new VehicleModelsExport(), 'PRIMA_MODELES_' . now()->format('Ymd_His') . '.xlsx');
    }
    public function importModels(Request $request) {
        $request->validate(['file' => 'required|file|mimes:xlsx,xls,csv|max:2048']);
        Excel::import(new VehicleModelsImport(), $request->file('file'));
        return back()->with('success', 'Import des modeles effectue avec succes.');
    }
    public function importBrandsModels(Request $request) {
        $request->validate(['file' => 'required|file|mimes:xlsx,xls,csv|max:2048']);
        Excel::import(new \App\Imports\BrandsModelsImport(), $request->file('file'));
        return back()->with('success', 'Import combine (marques + modeles + categories) effectue avec succes.');
    }

    // ===================== STRUCTURES =====================
    public function structures() {
        return view('referentials.structures', [
            'items' => Structure::with('directionRelation')->orderBy('code')->paginate(20),
            'directions' => Direction::where('is_active', true)->orderBy('name')->get(),
        ]);
    }
    public function storeStructure(Request $request) {
        $request->validate([
            'code' => 'required|string|max:10|unique:structures,code',
            'name' => 'required|string|max:100',
            'sigle' => 'nullable|string|max:20',
            'direction_id' => 'nullable|uuid|exists:directions,id',
            'site' => 'nullable|string|max:10',
            'type' => 'nullable|string|max:20',
            'region' => 'nullable|string|max:100',
        ]);
        Structure::create($request->only(['code', 'name', 'sigle', 'direction_id', 'site', 'type', 'region']));
        return back()->with('success', 'Structure ajoutee.');
    }
    public function updateStructure(Request $request, string $id) {
        Structure::findOrFail($id)->update($request->only(['code', 'name', 'sigle', 'direction_id', 'site', 'type', 'region', 'is_active']));
        return back()->with('success', 'Structure mise a jour.');
    }
    public function exportStructures() {
        return Excel::download(new StructuresExport(), 'PRIMA_STRUCTURES_' . now()->format('Ymd_His') . '.xlsx');
    }
    public function importStructures(Request $request) {
        $request->validate(['file' => 'required|file|mimes:xlsx,xls,csv|max:2048']);
        $import = new StructuresImport();
        Excel::import($import, $request->file('file'));
        $msg = "{$import->imported} structure(s) importee(s), {$import->updated} mise(s) a jour, {$import->skipped} ignoree(s).";
        if ($import->directionsCreated > 0) {
            $msg .= " {$import->directionsCreated} direction(s) creee(s).";
        }
        return back()->with('success', $msg);
    }

    // ===================== INSURANCES =====================
    public function insurances() { return view('referentials.insurances', ['items' => InsuranceCompany::orderBy('name')->paginate(20)]); }
    public function storeInsurance(Request $request) {
        $request->validate(['name' => 'required|string|max:100|unique:insurance_companies,name']);
        InsuranceCompany::create($request->only('name'));
        return back()->with('success', 'Compagnie ajoutee.');
    }
    public function updateInsurance(Request $request, string $id) {
        InsuranceCompany::findOrFail($id)->update($request->only(['name', 'is_active']));
        return back()->with('success', 'Compagnie mise a jour.');
    }
    public function exportInsurances() {
        return Excel::download(new InsuranceCompaniesExport(), 'PRIMA_ASSURANCES_' . now()->format('Ymd_His') . '.xlsx');
    }
    public function importInsurances(Request $request) {
        $request->validate(['file' => 'required|file|mimes:xlsx,xls,csv|max:2048']);
        Excel::import(new InsuranceCompaniesImport(), $request->file('file'));
        return back()->with('success', 'Import des compagnies effectue avec succes.');
    }

    // ===================== DIRECTIONS =====================
    public function directions() { return view('referentials.directions', ['items' => Direction::orderBy('name')->paginate(20)]); }
    public function storeDirection(Request $request) {
        $request->validate(['code' => 'required|string|max:10|unique:directions,code', 'name' => 'required|string|max:100']);
        Direction::create($request->only(['code', 'name']));
        return back()->with('success', 'Direction ajoutee.');
    }
    public function updateDirection(Request $request, string $id) {
        Direction::findOrFail($id)->update($request->only(['code', 'name', 'is_active']));
        return back()->with('success', 'Direction mise a jour.');
    }
    public function exportDirections() {
        return Excel::download(new DirectionsExport(), 'PRIMA_DIRECTIONS_' . now()->format('Ymd_His') . '.xlsx');
    }
    public function importDirections(Request $request) {
        $request->validate(['file' => 'required|file|mimes:xlsx,xls,csv|max:2048']);
        Excel::import(new DirectionsImport(), $request->file('file'));
        return back()->with('success', 'Import des directions effectue avec succes.');
    }

    // ===================== VEHICLE TYPES =====================
    public function vehicleTypes() { return view('referentials.vehicle-types', ['items' => VehicleType::orderBy('name')->paginate(20)]); }
    public function storeVehicleType(Request $request) {
        $request->validate(['name' => 'required|string|max:50|unique:vehicle_types,name']);
        VehicleType::create($request->only('name'));
        return back()->with('success', 'Type de vehicule ajoute.');
    }
    public function updateVehicleType(Request $request, string $id) {
        VehicleType::findOrFail($id)->update($request->only(['name', 'is_active']));
        return back()->with('success', 'Type de vehicule mis a jour.');
    }

    // ===================== VEHICLE CATEGORIES =====================
    public function vehicleCategories() { return view('referentials.vehicle-categories', ['items' => VehicleCategory::orderBy('name')->paginate(20), 'vehicleTypes' => VehicleType::where('is_active', true)->orderBy('name')->get()]); }
    public function storeVehicleCategory(Request $request) {
        $request->validate(['name' => 'required|string|max:50', 'vehicle_type' => 'nullable|string|max:50']);
        VehicleCategory::create($request->only(['name', 'vehicle_type']));
        return back()->with('success', 'Categorie ajoutee.');
    }
    public function updateVehicleCategory(Request $request, string $id) {
        VehicleCategory::findOrFail($id)->update($request->only(['name', 'vehicle_type', 'is_active']));
        return back()->with('success', 'Categorie mise a jour.');
    }
    public function exportVehicleCategories() {
        return Excel::download(new \App\Exports\VehicleCategoriesExport(), 'PRIMA_CATEGORIES_' . now()->format('Ymd_His') . '.xlsx');
    }
    public function importVehicleCategories(Request $request) {
        $request->validate(['file' => 'required|file|mimes:xlsx,xls,csv|max:2048']);
        Excel::import(new \App\Imports\VehicleCategoriesImport(), $request->file('file'));
        return back()->with('success', 'Import des categories effectue avec succes.');
    }

    // ===================== FUEL TYPES =====================
    public function fuelTypes() { return view('referentials.fuel-types', ['items' => FuelType::orderBy('name')->paginate(20)]); }
    public function storeFuelType(Request $request) {
        $request->validate(['name' => 'required|string|max:50|unique:fuel_types,name']);
        FuelType::create($request->only('name'));
        return back()->with('success', 'Type de carburant ajoute.');
    }
    public function updateFuelType(Request $request, string $id) {
        FuelType::findOrFail($id)->update($request->only(['name', 'is_active']));
        return back()->with('success', 'Type de carburant mis a jour.');
    }

    // ===================== TRANSMISSIONS =====================
    public function transmissionsList() { return view('referentials.transmissions', ['items' => Transmission::orderBy('name')->paginate(20)]); }
    public function storeTransmission(Request $request) {
        $request->validate(['name' => 'required|string|max:50|unique:transmissions,name']);
        Transmission::create($request->only('name'));
        return back()->with('success', 'Transmission ajoutee.');
    }
    public function updateTransmission(Request $request, string $id) {
        Transmission::findOrFail($id)->update($request->only(['name', 'is_active']));
        return back()->with('success', 'Transmission mise a jour.');
    }

    // ===================== VEHICLE STATUSES =====================
    public function vehicleStatuses() { return view('referentials.vehicle-statuses', ['items' => VehicleStatus::orderBy('name')->paginate(20)]); }
    public function storeVehicleStatus(Request $request) {
        $request->validate(['name' => 'required|string|max:50|unique:vehicle_statuses,name']);
        VehicleStatus::create($request->only('name'));
        return back()->with('success', 'Statut ajoute.');
    }
    public function updateVehicleStatus(Request $request, string $id) {
        VehicleStatus::findOrFail($id)->update($request->only(['name', 'is_active']));
        return back()->with('success', 'Statut mis a jour.');
    }

    // ===================== CONTRACT TYPES =====================
    public function contractTypes() { return view('referentials.contract-types', ['items' => ContractType::orderBy('name')->paginate(20)]); }
    public function storeContractType(Request $request) {
        $request->validate(['name' => 'required|string|max:50|unique:contract_types,name']);
        ContractType::create($request->only('name'));
        return back()->with('success', 'Type de contrat ajoute.');
    }
    public function updateContractType(Request $request, string $id) {
        ContractType::findOrFail($id)->update($request->only(['name', 'is_active']));
        return back()->with('success', 'Type de contrat mis a jour.');
    }

    // ===================== COLORS =====================
    public function colorsList() { return view('referentials.colors', ['items' => Color::orderBy('name')->paginate(20)]); }
    public function storeColor(Request $request) {
        $request->validate(['name' => 'required|string|max:50|unique:colors,name']);
        Color::create($request->only('name'));
        return back()->with('success', 'Couleur ajoutee.');
    }
    public function updateColor(Request $request, string $id) {
        Color::findOrFail($id)->update($request->only(['name', 'is_active']));
        return back()->with('success', 'Couleur mise a jour.');
    }
}
