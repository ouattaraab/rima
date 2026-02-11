<?php

namespace App\Imports;

use App\Models\Brand;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class BrandsImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        $name = trim($row['nom'] ?? '');
        if (empty($name)) {
            return null;
        }

        Brand::updateOrCreate(
            ['name' => $name],
            ['is_active' => true]
        );

        return null;
    }
}
