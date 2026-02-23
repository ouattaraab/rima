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
        // Support both old format (code/nom/region) and SODECI format (ci/exploitation/sigle_direction/libelle_direction/site/type_direction)
        $code = trim($row['ci'] ?? $row['code'] ?? '');
        $name = trim($row['exploitation'] ?? $row['nom'] ?? '');

        if (empty($code) || empty($name)) {
            $this->skipped++;
            return null;
        }

        $sigle = trim($row['sigle_direction'] ?? '') ?: null;
        $region = trim($row['libelle_direction'] ?? $row['region'] ?? '') ?: null;
        $site = trim($row['site'] ?? '') ?: null;
        $type = trim($row['type_direction'] ?? '') ?: null;

        $data = [
            'name' => $name,
            'sigle' => $sigle,
            'region' => $region,
            'direction' => $region,
            'site' => $site,
            'type' => $type,
            'is_active' => true,
        ];

        $existing = Structure::where('code', $code)->first();
        if ($existing) {
            $existing->update($data);
            $this->updated++;
        } else {
            Structure::create(array_merge(['code' => $code], $data));
            $this->imported++;
        }

        return null;
    }
}
