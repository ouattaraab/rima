<?php

namespace App\Imports;

use App\Models\Structure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class StructuresImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        $code = trim($row['code'] ?? '');
        $name = trim($row['nom'] ?? '');

        if (empty($code) || empty($name)) {
            return null;
        }

        Structure::updateOrCreate(
            ['code' => $code],
            [
                'name' => $name,
                'region' => trim($row['region'] ?? '') ?: null,
                'is_active' => true,
            ]
        );

        return null;
    }
}
