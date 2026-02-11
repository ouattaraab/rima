<?php

namespace App\Imports;

use App\Models\Brand;
use App\Models\VehicleModel;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class VehicleModelsImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        $brandName = trim($row['marque'] ?? '');
        $name = trim($row['nom'] ?? '');

        if (empty($name) || empty($brandName)) {
            return null;
        }

        $brand = Brand::where('name', $brandName)->first();
        if (!$brand) {
            return null;
        }

        VehicleModel::updateOrCreate(
            ['brand_id' => $brand->id, 'name' => $name],
            [
                'category' => trim($row['categorie'] ?? '') ?: null,
                'is_active' => true,
            ]
        );

        return null;
    }
}
