<?php

use App\Http\Controllers\Web\LoginController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\VehicleController;
use App\Http\Controllers\Web\ReferentialController;
use App\Http\Controllers\Web\UserController;
use App\Http\Controllers\Web\AuditController;
use App\Http\Controllers\Web\ReportController;
use App\Http\Controllers\Web\NotificationController;
use Illuminate\Support\Facades\Route;

// ============ PUBLIC ============
Route::redirect('/', '/login');
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->middleware('audit');
Route::get('/privacy-policy', fn () => view('public.privacy-policy'))->name('privacy-policy');

// ============ AUTHENTIFIE ============
Route::middleware(['auth', 'audit'])->group(function () {

    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // --- Dashboard ---
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // --- Vehicules (tous les authentifies) ---
    Route::get('/vehicles', [VehicleController::class, 'index'])->name('vehicles.index');
    Route::get('/vehicles/export', [VehicleController::class, 'export'])->name('vehicles.export');
    Route::get('/vehicles/export-pdf', [VehicleController::class, 'exportPdf'])->name('vehicles.exportPdf');
    Route::get('/vehicles/motos/template', [VehicleController::class, 'motosTemplate'])->name('vehicles.motos.template');
    Route::post('/vehicles/motos/import', [VehicleController::class, 'importMotos'])->name('vehicles.motos.import');
    Route::get('/vehicles/{vehicle}', [VehicleController::class, 'show'])->name('vehicles.show');
    Route::get('/vehicles/{vehicle}/pdf', [VehicleController::class, 'downloadPdf'])->name('vehicles.downloadPdf');

    // --- Validation/Rejet (supervisor_sodeci + admin_sodeci + validateur_sodeci) ---
    Route::middleware('role:supervisor_sodeci,admin_sodeci,validateur_sodeci')->group(function () {
        Route::post('/vehicles/{vehicle}/validate', [VehicleController::class, 'validateVehicle'])->name('vehicles.validate');
        Route::post('/vehicles/{vehicle}/reject', [VehicleController::class, 'reject'])->name('vehicles.reject');
    });

    // --- Financier (admin_sodeci + finance_dbcg + finance_dfc) ---
    Route::middleware('role:admin_sodeci,finance_dbcg,finance_dfc')->group(function () {
        Route::get('/vehicles/{vehicle}/financial', [VehicleController::class, 'financial'])->name('vehicles.financial');
        Route::put('/vehicles/{vehicle}/financial', [VehicleController::class, 'updateFinancial'])->name('vehicles.updateFinancial');
    });

    // --- Edition admin (admin_sodeci uniquement) ---
    Route::middleware('role:admin_sodeci')->group(function () {
        Route::get('/vehicles/{vehicle}/edit', [VehicleController::class, 'edit'])->name('vehicles.edit');
        Route::put('/vehicles/{vehicle}', [VehicleController::class, 'update'])->name('vehicles.update');
    });

    // --- Referentiels (admin_sodeci uniquement) ---
    Route::middleware('role:admin_sodeci')->prefix('referentials')->name('referentials.')->group(function () {
        Route::get('/brands', [ReferentialController::class, 'brands'])->name('brands');
        Route::post('/brands', [ReferentialController::class, 'storeBrand'])->name('brands.store');
        Route::put('/brands/{brand}', [ReferentialController::class, 'updateBrand'])->name('brands.update');
        Route::get('/brands/export', [ReferentialController::class, 'exportBrands'])->name('brands.export');
        Route::post('/brands/import', [ReferentialController::class, 'importBrands'])->name('brands.import');

        Route::get('/models', [ReferentialController::class, 'models'])->name('models');
        Route::post('/models', [ReferentialController::class, 'storeModel'])->name('models.store');
        Route::put('/models/{model}', [ReferentialController::class, 'updateModel'])->name('models.update');
        Route::get('/models/export', [ReferentialController::class, 'exportModels'])->name('models.export');
        Route::post('/models/import', [ReferentialController::class, 'importModels'])->name('models.import');
        Route::post('/models/import-combined', [ReferentialController::class, 'importBrandsModels'])->name('models.import-combined');

        Route::get('/structures', [ReferentialController::class, 'structures'])->name('structures');
        Route::post('/structures', [ReferentialController::class, 'storeStructure'])->name('structures.store');
        Route::put('/structures/{structure}', [ReferentialController::class, 'updateStructure'])->name('structures.update');
        Route::get('/structures/export', [ReferentialController::class, 'exportStructures'])->name('structures.export');
        Route::post('/structures/import', [ReferentialController::class, 'importStructures'])->name('structures.import');

        Route::get('/insurances', [ReferentialController::class, 'insurances'])->name('insurances');
        Route::post('/insurances', [ReferentialController::class, 'storeInsurance'])->name('insurances.store');
        Route::put('/insurances/{insurance}', [ReferentialController::class, 'updateInsurance'])->name('insurances.update');
        Route::get('/insurances/export', [ReferentialController::class, 'exportInsurances'])->name('insurances.export');
        Route::post('/insurances/import', [ReferentialController::class, 'importInsurances'])->name('insurances.import');

        // Route::get('/directions', [ReferentialController::class, 'directions'])->name('directions');
        // Route::post('/directions', [ReferentialController::class, 'storeDirection'])->name('directions.store');
        // Route::put('/directions/{direction}', [ReferentialController::class, 'updateDirection'])->name('directions.update');
        // Route::get('/directions/export', [ReferentialController::class, 'exportDirections'])->name('directions.export');
        // Route::post('/directions/import', [ReferentialController::class, 'importDirections'])->name('directions.import');

        Route::get('/vehicle-types', [ReferentialController::class, 'vehicleTypes'])->name('vehicle-types');
        Route::post('/vehicle-types', [ReferentialController::class, 'storeVehicleType'])->name('vehicle-types.store');
        Route::put('/vehicle-types/{id}', [ReferentialController::class, 'updateVehicleType'])->name('vehicle-types.update');

        Route::get('/vehicle-categories', [ReferentialController::class, 'vehicleCategories'])->name('vehicle-categories');
        Route::post('/vehicle-categories', [ReferentialController::class, 'storeVehicleCategory'])->name('vehicle-categories.store');
        Route::put('/vehicle-categories/{id}', [ReferentialController::class, 'updateVehicleCategory'])->name('vehicle-categories.update');
        Route::get('/vehicle-categories/export', [ReferentialController::class, 'exportVehicleCategories'])->name('vehicle-categories.export');
        Route::post('/vehicle-categories/import', [ReferentialController::class, 'importVehicleCategories'])->name('vehicle-categories.import');

        Route::get('/fuel-types', [ReferentialController::class, 'fuelTypes'])->name('fuel-types');
        Route::post('/fuel-types', [ReferentialController::class, 'storeFuelType'])->name('fuel-types.store');
        Route::put('/fuel-types/{id}', [ReferentialController::class, 'updateFuelType'])->name('fuel-types.update');

        Route::get('/transmissions', [ReferentialController::class, 'transmissionsList'])->name('transmissions');
        Route::post('/transmissions', [ReferentialController::class, 'storeTransmission'])->name('transmissions.store');
        Route::put('/transmissions/{id}', [ReferentialController::class, 'updateTransmission'])->name('transmissions.update');

        Route::get('/vehicle-statuses', [ReferentialController::class, 'vehicleStatuses'])->name('vehicle-statuses');
        Route::post('/vehicle-statuses', [ReferentialController::class, 'storeVehicleStatus'])->name('vehicle-statuses.store');
        Route::put('/vehicle-statuses/{id}', [ReferentialController::class, 'updateVehicleStatus'])->name('vehicle-statuses.update');

        Route::get('/contract-types', [ReferentialController::class, 'contractTypes'])->name('contract-types');
        Route::post('/contract-types', [ReferentialController::class, 'storeContractType'])->name('contract-types.store');
        Route::put('/contract-types/{id}', [ReferentialController::class, 'updateContractType'])->name('contract-types.update');

        Route::get('/colors', [ReferentialController::class, 'colorsList'])->name('colors');
        Route::post('/colors', [ReferentialController::class, 'storeColor'])->name('colors.store');
        Route::put('/colors/{id}', [ReferentialController::class, 'updateColor'])->name('colors.update');

        Route::get('/banks', [ReferentialController::class, 'banksList'])->name('banks');
        Route::post('/banks', [ReferentialController::class, 'storeBank'])->name('banks.store');
        Route::put('/banks/{bank}', [ReferentialController::class, 'updateBank'])->name('banks.update');
    });

    // --- Utilisateurs (admin_sodeci uniquement) ---
    Route::middleware('role:admin_sodeci')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/export', [UserController::class, 'export'])->name('users.export');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::post('/users/{user}/unlock', [UserController::class, 'unlock'])->name('users.unlock');
    });

    // --- Rapports (supervisor_sodeci + admin_sodeci) ---
    Route::middleware('role:supervisor_sodeci,admin_sodeci')->prefix('reports')->name('reports.')->group(function () {
        Route::get('/regional', [ReportController::class, 'regional'])->name('regional');
        Route::get('/regional/export', [ReportController::class, 'regionalExport'])->name('regional.export');
        Route::get('/compliance', [ReportController::class, 'compliance'])->name('compliance');
        Route::get('/compliance/export', [ReportController::class, 'complianceExport'])->name('compliance.export');
        Route::get('/agents', [ReportController::class, 'agents'])->name('agents');
        Route::get('/agents/{user}', [ReportController::class, 'agentShow'])->name('agents.show');
    });

    // --- Notifications (tous les authentifies) ---
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.readAll');

    // --- Audit (admin_sodeci uniquement) ---
    Route::middleware('role:admin_sodeci')->group(function () {
        Route::get('/audit', [AuditController::class, 'index'])->name('audit.index');
        Route::get('/audit/export', [AuditController::class, 'export'])->name('audit.export');
    });
});
