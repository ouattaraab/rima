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
            'temporary_registration' => !empty($row['immatriculation_provisoire']) ? trim($row['immatriculation_provisoire']) : null,
            'chassis_number' => $chassisNumber ?: null,
            'chassis_readable' => true,
            'engine_displacement' => !empty($row['cylindree']) ? (int)$row['cylindree'] : null,
            'color' => trim($row['couleur'] ?? ''),
            'fuel_type' => trim($row['carburant'] ?? ''),
            'transmission' => !empty($row['transmission']) ? trim($row['transmission']) : null,
            'seats_count' => !empty($row['places']) ? (int)$row['places'] : 2,
            'load_capacity' => !empty($row['charge_utile']) ? (int)$row['charge_utile'] : null,
            'mileage' => !empty($row['kilometrage']) ? (int)$row['kilometrage'] : 0,
            'status' => trim($row['statut'] ?? 'En service'),
            'structure_ci' => trim($row['structure_ci'] ?? ''),
            'special_equipment' => !empty($row['equipements_speciaux']) ? trim($row['equipements_speciaux']) : null,
            'commissioning_date' => !empty($row['date_mise_en_circulation']) ? $row['date_mise_en_circulation'] : null,
            'contract_type' => trim($row['type_contrat'] ?? ''),
            'technical_inspection_date' => !empty($row['date_controle_technique']) ? $row['date_controle_technique'] : null,
            'is_insured' => !empty($row['assure']) ? (strtolower(trim($row['assure'])) === 'oui') : false,
            'insurance_company' => !empty($row['compagnie_assurance']) ? trim($row['compagnie_assurance']) : null,
            'policy_number' => !empty($row['numero_police']) ? trim($row['numero_police']) : null,
            'insurance_start_date' => !empty($row['debut_assurance']) ? $row['debut_assurance'] : null,
            'insurance_end_date' => !empty($row['fin_assurance']) ? $row['fin_assurance'] : null,
            'user_matricule' => !empty($row['matricule_agent']) ? trim($row['matricule_agent']) : null,
            'user_direction' => !empty($row['direction']) ? trim($row['direction']) : null,
            'form_status' => 'synchronized',
            'collected_at' => now(),
        ]);
    }
}
