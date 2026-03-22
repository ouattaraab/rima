<?php

namespace App\Imports;

use App\Models\Structure;
use App\Models\Vehicle;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

class MotosImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
{
    public int $imported = 0;
    public int $skipped = 0;
    public array $errors = [];
    public array $duplicates = [];

    public function __construct(private string $userId) {}

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $row = $row->toArray();
            $ref = trim($row['immatriculation'] ?? $row['n_chassis'] ?? '???');

            try {
                $this->processRow($row);
            } catch (\Exception $e) {
                $this->errors[] = "Ligne [{$ref}]: {$e->getMessage()}";
                Log::warning('MotosImport row failed', ['ref' => $ref, 'error' => $e->getMessage()]);
                $this->skipped++;
            }
        }
    }

    private function processRow(array $row): void
    {
        $registrationNumber = trim($row['immatriculation'] ?? '');
        $chassisNumber = trim($row['n_chassis'] ?? $row['chassis'] ?? '');

        // Skip if no registration number and no chassis number
        if (empty($registrationNumber) && empty($chassisNumber)) {
            $this->skipped++;
            return;
        }

        // Check for duplicates with detailed motifs
        $duplicateReasons = [];
        $ref = $registrationNumber ?: $chassisNumber;

        if (!empty($registrationNumber) && Vehicle::where('registration_number', $registrationNumber)->exists()) {
            $duplicateReasons[] = "immatriculation '{$registrationNumber}' deja existante";
        }
        if (!empty($chassisNumber) && Vehicle::where('chassis_number', $chassisNumber)->exists()) {
            $duplicateReasons[] = "chassis '{$chassisNumber}' deja existant";
        }

        if (!empty($duplicateReasons)) {
            $this->skipped++;
            $this->duplicates[] = "[{$ref}]: " . implode(', ', $duplicateReasons);
            return;
        }

        Vehicle::create([
            'id' => Str::uuid()->toString(),
            'vehicle_type' => 'Moto',
            'category' => trim($row['categorie'] ?? 'Moto') ?: 'Moto',
            'brand' => trim($row['marque'] ?? '') ?: 'Inconnu',
            'model' => trim($row['modele'] ?? '') ?: 'Inconnu',
            'registration_number' => $registrationNumber ?: null,
            'temporary_registration' => !empty($row['immatriculation_provisoire']) ? trim($row['immatriculation_provisoire']) : null,
            'chassis_number' => $chassisNumber ?: null,
            'chassis_readable' => true,
            'engine_displacement' => !empty($row['cylindree']) ? (int) $row['cylindree'] : null,
            'color' => trim($row['couleur'] ?? '') ?: 'Autre',
            'fuel_type' => $this->validEnum($row['carburant'] ?? '', ['Essence', 'Gasoil', 'Hybride', 'Electrique'], 'Essence'),
            'transmission' => !empty($row['transmission']) ? $this->validEnum($row['transmission'], ['Automatique', 'Manuelle'], null) : null,
            'seats_count' => !empty($row['places']) ? min((int) $row['places'], 2) : 2,
            'load_capacity' => !empty($row['charge_utile']) ? (int) $row['charge_utile'] : null,
            'mileage' => !empty($row['kilometrage']) ? max((int) $row['kilometrage'], 1) : 1,
            'status' => $this->validEnum($row['statut'] ?? '', ['En service', 'En reparation', 'Reforme', 'Cede'], 'En service'),
            'structure_ci' => $this->extractStructureCode($row['structure'] ?? ''),
            'special_equipment' => !empty($row['equipements_speciaux']) ? trim($row['equipements_speciaux']) : null,
            'commissioning_date' => $this->parseDate($row['date_mise_en_circulation'] ?? '') ?: now()->toDateString(),
            'contract_type' => $this->validEnum($row['type_contrat'] ?? '', ['Sous contrat', 'Flotte'], 'Flotte'),
            'technical_inspection_date' => $this->parseDate($row['date_controle_technique'] ?? '') ?: now()->toDateString(),
            'is_insured' => !empty($row['assure']) ? (strtolower(trim($row['assure'])) === 'oui') : false,
            'insurance_company' => !empty($row['compagnie_assurance']) ? trim($row['compagnie_assurance']) : null,
            'policy_number' => !empty($row['numero_police']) ? trim($row['numero_police']) : null,
            'insurance_start_date' => $this->parseDate($row['debut_assurance'] ?? ''),
            'insurance_end_date' => $this->parseDate($row['fin_assurance'] ?? ''),
            'user_matricule' => !empty($row['matricule_conducteur']) ? trim($row['matricule_conducteur']) : (!empty($row['matricule_agent']) ? trim($row['matricule_agent']) : null),
            'user_driver_license' => !empty($row['permis_conducteur']) ? trim($row['permis_conducteur']) : null,
            'user_direction' => $this->extractDirectionFromStructure($row['structure'] ?? ''),
            'form_status' => 'synchronized',
            'data_origin' => 'import',
            'collected_by' => $this->userId,
            'collected_at' => now(),
        ]);

        $this->imported++;
    }

    /**
     * Parse a date in various formats (dd/mm/yyyy, yyyy-mm-dd, Excel numeric, Carbon/DateTime).
     */
    private function parseDate(mixed $value): ?string
    {
        if (empty($value)) {
            return null;
        }

        // Already a DateTime/Carbon object (Excel stores dates as objects)
        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance($value)->format('Y-m-d');
        }

        // Excel numeric date (e.g. 45678)
        if (is_numeric($value)) {
            return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((int) $value)->format('Y-m-d');
        }

        $value = trim((string) $value);
        if (empty($value)) {
            return null;
        }

        // dd/mm/yyyy or dd-mm-yyyy
        if (preg_match('#^(\d{1,2})[/\-](\d{1,2})[/\-](\d{4})$#', $value, $m)) {
            return sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1]);
        }

        // yyyy-mm-dd (already correct)
        if (preg_match('#^\d{4}-\d{2}-\d{2}$#', $value)) {
            return $value;
        }

        // Last resort: try Carbon::parse
        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    private function validEnum(string $value, array $allowed, ?string $default): ?string
    {
        $value = trim($value);
        return in_array($value, $allowed) ? $value : $default;
    }

    private function extractStructureCode(string $value): ?string
    {
        $value = trim($value);
        if (empty($value)) {
            return null;
        }

        if (str_contains($value, '-')) {
            return trim(explode('-', $value, 2)[0]);
        }

        return $value;
    }

    private function extractDirectionFromStructure(string $value): ?string
    {
        $structureCode = $this->extractStructureCode($value);
        if (empty($structureCode)) {
            return null;
        }

        $structure = Structure::where('code', $structureCode)->first();
        if ($structure && !empty($structure->direction)) {
            return $structure->direction;
        }

        return null;
    }
}
