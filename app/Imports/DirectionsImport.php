<?php

namespace App\Imports;

use App\Models\Direction;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class DirectionsImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        $code = trim($row['code'] ?? '');
        $name = trim($row['nom'] ?? '');

        if (empty($code) || empty($name)) {
            return null;
        }

        Direction::updateOrCreate(
            ['code' => $code],
            [
                'name' => $name,
                'is_active' => true,
            ]
        );

        return null;
    }
}
