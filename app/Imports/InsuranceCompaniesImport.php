<?php

namespace App\Imports;

use App\Models\InsuranceCompany;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class InsuranceCompaniesImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        $name = trim($row['nom'] ?? '');
        if (empty($name)) {
            return null;
        }

        InsuranceCompany::updateOrCreate(
            ['name' => $name],
            ['is_active' => true]
        );

        return null;
    }
}
