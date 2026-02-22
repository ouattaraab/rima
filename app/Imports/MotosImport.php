<?php

namespace App\Imports;

use App\Models\Vehicle;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Illuminate\Support\Str;

class MotosImport implements ToModel, WithHeadingRow, SkipsEmptyRows
{
    public int $imported = 0;
    public int $skipped = 0;
    public array $errors = [];

    public function model(array $row)
    {
        $registrationNumber = trim($row['immatriculation'] ?? '');
        $chassisNumber = trim($row['n_chassis'] ?? $row['chassis'] ?? '');

        // Skip if no registration number and no chassis number
        if (empty($registrationNumber) && empty($chassisNumber)) {
            $this->skipped++;
            return null;
        }

        // Check for duplicates
        $exists = Vehicle::query();
        if (!empty($registrationNumber)) {
            $exists->where('registration_number', $registrationNumber);
        }
        if (!empty($chassisNumber)) {
            $exists->orWhere('chassis_number', $chassisNumber);
        }
        if ($exists->exists()) {
            $this->skipped++;
            return null;
        }

        $this->imported++;

        return new Vehicle([
            'id' => Str::uuid()->toString(),
            'vehicle_type' => 'Moto',
            'category' => trim($row['categorie'] ?? 'Moto'),
            'brand' => trim($row['marque'] ?? ''),
            'model' => trim($row['modele'] ?? ''),
            'registration_number' => $registrationNumber ?: null,
            'chassis_number' => $chassisNumber ?: null,
            'engine_displacement' => !empty($row['cylindree']) ? (int)$row['cylindree'] : null,
            'color' => trim($row['couleur'] ?? ''),
            'fuel_type' => trim($row['carburant'] ?? ''),
            'seats_count' => !empty($row['places']) ? (int)$row['places'] : 2,
            'mileage' => !empty($row['kilometrage']) ? (int)$row['kilometrage'] : 0,
            'status' => trim($row['statut'] ?? 'En service'),
            'commissioning_date' => !empty($row['date_mise_en_circulation']) ? $row['date_mise_en_circulation'] : null,
            'contract_type' => trim($row['type_contrat'] ?? ''),
            'structure_ci' => trim($row['structure_ci'] ?? ''),
            'form_status' => 'synchronized',
            'collected_at' => now(),
        ]);
    }
}
