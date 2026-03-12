<?php

use App\Http\Controllers\Api\V1\AdminController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\MediaController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\OcrController;
use App\Http\Controllers\Api\V1\ReferentialController;
use App\Http\Controllers\Api\V1\SodeciVehicleController;
use App\Http\Controllers\Api\V1\VehicleController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| PRIMA API Routes - v1
|--------------------------------------------------------------------------
| Prefix: /api/v1 (configure dans bootstrap/app.php)
*/

// ============ PUBLIC ============
Route::post('/auth/login', [AuthController::class, 'login']);

// ============ AUTHENTIFIE (Sanctum) ============
Route::middleware(['auth:sanctum', 'audit'])->group(function () {

    // --- Auth ---
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);

    // --- Vehicules CIDEC (agents + admin) ---
    Route::middleware('role:agent_cidec,admin_sodeci')->group(function () {
        Route::get('/vehicles', [VehicleController::class, 'index']);
        Route::post('/vehicles', [VehicleController::class, 'store']);
        Route::post('/vehicles/find-existing', [VehicleController::class, 'findExisting']);
        Route::get('/vehicles/{vehicle}', [VehicleController::class, 'show']);
        Route::put('/vehicles/{vehicle}', [VehicleController::class, 'update']);
        Route::delete('/vehicles/{vehicle}', [VehicleController::class, 'destroy']);
        Route::post('/vehicles/{vehicle}/sync', [VehicleController::class, 'sync']);
        Route::post('/vehicles/batch-sync', [VehicleController::class, 'batchSync']);
    });

    // --- Media (agents + admin) ---
    Route::middleware('role:agent_cidec,admin_sodeci')->group(function () {
        Route::post('/media/upload', [MediaController::class, 'upload']);
        Route::delete('/media/{photo}', [MediaController::class, 'destroy']);
    });

    // --- OCR (agents + admin) ---
    Route::middleware('role:agent_cidec,admin_sodeci')->group(function () {
        Route::post('/ocr/registration', [OcrController::class, 'recognizeRegistration']);
        Route::post('/ocr/chassis', [OcrController::class, 'recognizeChassis']);
    });

    // Media show: tous les authentifies
    Route::get('/media/{photo}', [MediaController::class, 'show']);

    // --- Referentiels (lecture: tous | ecriture: admin) ---
    Route::get('/referentials/brands', [ReferentialController::class, 'brands']);
    Route::get('/referentials/models', [ReferentialController::class, 'models']);
    Route::get('/referentials/structures', [ReferentialController::class, 'structures']);
    Route::get('/referentials/insurance-companies', [ReferentialController::class, 'insuranceCompanies']);
    Route::get('/referentials/directions', [ReferentialController::class, 'directions']);
    Route::get('/referentials/vehicle-types', [ReferentialController::class, 'vehicleTypes']);
    Route::get('/referentials/vehicle-categories', [ReferentialController::class, 'vehicleCategories']);
    Route::get('/referentials/fuel-types', [ReferentialController::class, 'fuelTypes']);
    Route::get('/referentials/transmissions', [ReferentialController::class, 'transmissions']);
    Route::get('/referentials/vehicle-statuses', [ReferentialController::class, 'vehicleStatuses']);
    Route::get('/referentials/contract-types', [ReferentialController::class, 'contractTypes']);
    Route::get('/referentials/colors', [ReferentialController::class, 'colors']);

    Route::middleware('role:admin_sodeci')->group(function () {
        Route::post('/referentials/brands', [ReferentialController::class, 'storeBrand']);
        Route::put('/referentials/brands/{brand}', [ReferentialController::class, 'updateBrand']);
        Route::post('/referentials/models', [ReferentialController::class, 'storeModel']);
        Route::put('/referentials/models/{model}', [ReferentialController::class, 'updateModel']);
        Route::post('/referentials/structures', [ReferentialController::class, 'storeStructure']);
        Route::put('/referentials/structures/{structure}', [ReferentialController::class, 'updateStructure']);
        Route::post('/referentials/insurance-companies', [ReferentialController::class, 'storeInsuranceCompany']);
        Route::put('/referentials/insurance-companies/{company}', [ReferentialController::class, 'updateInsuranceCompany']);
        Route::post('/referentials/directions', [ReferentialController::class, 'storeDirection']);
        Route::put('/referentials/directions/{direction}', [ReferentialController::class, 'updateDirection']);
        Route::post('/referentials/vehicle-types', [ReferentialController::class, 'storeVehicleType']);
        Route::put('/referentials/vehicle-types/{id}', [ReferentialController::class, 'updateVehicleType']);
        Route::post('/referentials/vehicle-categories', [ReferentialController::class, 'storeVehicleCategory']);
        Route::put('/referentials/vehicle-categories/{id}', [ReferentialController::class, 'updateVehicleCategory']);
        Route::post('/referentials/fuel-types', [ReferentialController::class, 'storeFuelType']);
        Route::put('/referentials/fuel-types/{id}', [ReferentialController::class, 'updateFuelType']);
        Route::post('/referentials/transmissions', [ReferentialController::class, 'storeTransmission']);
        Route::put('/referentials/transmissions/{id}', [ReferentialController::class, 'updateTransmission']);
        Route::post('/referentials/vehicle-statuses', [ReferentialController::class, 'storeVehicleStatus']);
        Route::put('/referentials/vehicle-statuses/{id}', [ReferentialController::class, 'updateVehicleStatus']);
        Route::post('/referentials/contract-types', [ReferentialController::class, 'storeContractType']);
        Route::put('/referentials/contract-types/{id}', [ReferentialController::class, 'updateContractType']);
        Route::post('/referentials/colors', [ReferentialController::class, 'storeColor']);
        Route::put('/referentials/colors/{id}', [ReferentialController::class, 'updateColor']);
    });

    // --- SODECI Vehicules (superviseurs + admin) ---
    Route::middleware('role:supervisor_cidec,supervisor_sodeci,admin_sodeci')->group(function () {
        Route::get('/sodeci/vehicles', [SodeciVehicleController::class, 'index']);
        Route::get('/sodeci/vehicles/{vehicle}', [SodeciVehicleController::class, 'show']);
    });

    Route::middleware('role:supervisor_sodeci,admin_sodeci')->group(function () {
        Route::post('/sodeci/vehicles/{vehicle}/validate', [SodeciVehicleController::class, 'validateVehicle']);
        Route::post('/sodeci/vehicles/{vehicle}/reject', [SodeciVehicleController::class, 'reject']);
        Route::get('/sodeci/vehicles-export', [SodeciVehicleController::class, 'export']);
    });

    Route::middleware('role:admin_sodeci')->group(function () {
        Route::put('/sodeci/vehicles/{vehicle}', [SodeciVehicleController::class, 'update']);
        Route::put('/sodeci/vehicles/{vehicle}/financial', [SodeciVehicleController::class, 'updateFinancial']);
    });

    // --- Dashboard (superviseurs + admin) ---
    Route::middleware('role:supervisor_cidec,supervisor_sodeci,admin_sodeci')->group(function () {
        Route::get('/dashboard/stats', [DashboardController::class, 'stats']);
        Route::get('/dashboard/by-agent', [DashboardController::class, 'byAgent']);
        Route::get('/dashboard/map', [DashboardController::class, 'map']);
    });

    // --- Notifications (tous les authentifies) ---
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);

    // --- Admin (admin_sodeci uniquement) ---
    Route::middleware('role:admin_sodeci')->group(function () {
        Route::get('/admin/users', [AdminController::class, 'users']);
        Route::post('/admin/users', [AdminController::class, 'storeUser']);
        Route::put('/admin/users/{user}', [AdminController::class, 'updateUser']);
        Route::get('/admin/audit-logs', [AdminController::class, 'auditLogs']);
    });
});
