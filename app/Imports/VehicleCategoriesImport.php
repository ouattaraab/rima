<?php

namespace App\Imports;

use App\Models\VehicleCategory;
use App\Models\VehicleType;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class VehicleCategoriesImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        $name = trim($row['nom'] ?? $row['name'] ?? $row['categorie'] ?? '');
        $vehicleType = trim($row['type_vehicule'] ?? $row['type'] ?? $row['vehicle_type'] ?? '');

        if (empty($name)) {
            return null;
        }

        // Auto-create vehicle type if provided and doesn't exist
        if (!empty($vehicleType)) {
            VehicleType::firstOrCreate(
                ['name' => $vehicleType],
                ['is_active' => true]
            );
        }

        VehicleCategory::updateOrCreate(
            ['name' => $name],
            [
                'vehicle_type' => $vehicleType ?: null,
                'is_active' => true,
            ]
        );

        return null;
    }
}
