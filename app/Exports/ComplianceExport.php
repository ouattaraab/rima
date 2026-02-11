<?php

namespace App\Exports;

use App\Models\Vehicle;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ComplianceExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            'Assurance expiree' => new ComplianceSheet('expired_insurance'),
            'Controle technique' => new ComplianceSheet('expired_inspection'),
            'Sans immatriculation' => new ComplianceSheet('no_registration'),
            'Sans assurance' => new ComplianceSheet('no_insurance'),
        ];
    }
}
