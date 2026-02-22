<?php

namespace App\Imports;

use App\Models\Structure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class StructuresImport implements ToModel, WithHeadingRow
{
    public int $imported = 0;
    public int $updated = 0;
    public int $skipped = 0;

    public function model(array $row)
    {
        // Support both old format (code/nom/region) and SODECI format (ci/exploitation/libelle_direction/site/type_direction)
        $code = trim($row['ci'] ?? $row['code'] ?? '');
        $name = trim($row['exploitation'] ?? $row['nom'] ?? '');

        if (empty($code) || empty($name)) {
            $this->skipped++;
            return null;
        }

        $region = trim($row['libelle_direction'] ?? $row['region'] ?? '') ?: null;

        $existing = Structure::where('code', $code)->first();
        if ($existing) {
            $existing->update([
                'name' => $name,
                'region' => $region,
                'is_active' => true,
            ]);
            $this->updated++;
        } else {
            Structure::create([
                'code' => $code,
                'name' => $name,
                'region' => $region,
                'is_active' => true,
            ]);
            $this->imported++;
        }

        return null;
    }
}
