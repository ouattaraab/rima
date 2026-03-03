<?php

namespace App\Imports;

use App\Models\Brand;
use App\Models\VehicleCategory;
use App\Models\VehicleModel;
use App\Models\VehicleType;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class BrandsModelsImport implements ToModel, WithHeadingRow
{
    public function headingRow(): int
    {
        return 3;
    }

    public function model(array $row)
    {
        $brandName   = trim($row['marque'] ?? '');
        $modelName   = trim($row['modele'] ?? '');
        $category    = trim($row['categorie'] ?? '');
        $vehicleType = trim($row['type_vehicule'] ?? $row['type'] ?? '');

        if (empty($brandName) || empty($modelName)) {
            return null;
        }

        $brand = Brand::firstOrCreate(
            ['name' => $brandName],
            ['is_active' => true]
        );

        if (!empty($category)) {
            // Auto-create vehicle type if provided
            if (!empty($vehicleType)) {
                VehicleType::firstOrCreate(
                    ['name' => $vehicleType],
                    ['is_active' => true]
                );
            }

            VehicleCategory::updateOrCreate(
                ['name' => $category],
                [
                    'vehicle_type' => $vehicleType ?: null,
                    'is_active' => true,
                ]
            );
        }

        VehicleModel::updateOrCreate(
            ['brand_id' => $brand->id, 'name' => $modelName],
            ['category' => $category ?: null, 'is_active' => true]
        );

        return null;
    }
}
