<?php

namespace App\Exports;

use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class VehiclesExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function query(): Builder
    {
        $query = Vehicle::with('collector');

        if (!empty($this->filters['form_status'])) {
            $query->where('form_status', $this->filters['form_status']);
        }
        if (!empty($this->filters['brand'])) {
            $query->where('brand', $this->filters['brand']);
        }
        if (!empty($this->filters['vehicle_type'])) {
            $query->where('vehicle_type', $this->filters['vehicle_type']);
        }
        if (!empty($this->filters['category'])) {
            $query->where('category', $this->filters['category']);
        }
        if (!empty($this->filters['vehicle_status'])) {
            $query->where('status', $this->filters['vehicle_status']);
        }
        if (!empty($this->filters['date_from'])) {
            $query->whereDate('collected_at', '>=', $this->filters['date_from']);
        }
        if (!empty($this->filters['date_to'])) {
            $query->whereDate('collected_at', '<=', $this->filters['date_to']);
        }

        return $query->orderByDesc('collected_at');
    }

    public function headings(): array
    {
        return [
            'Type', 'Categorie', 'Marque', 'Modele', 'Version', 'Couleur',
            'Date mise en circulation', 'Type contrat',
            'Immatriculation', 'Immat. provisoire', 'N° Chassis', 'Chassis lisible',
            'Carburant', 'Transmission', 'Cylindree', 'Nb places', 'Charge utile (kg)', 'Kilometrage',
            'Statut vehicule', 'Structure/CI', 'Arceaux', 'Equipements speciaux',
            'Date controle technique',
            'Assure', 'Compagnie assurance', 'N° police', 'Type couverture',
            'Debut assurance', 'Fin assurance',
            'Direction', 'Matricule utilisateur', 'N° Permis',
            'Mode financement', 'Banque', 'N° contrat', 'Debut prelevement',
            'Fin prelevement', 'Debut contrat', 'Date mise a disposition',
            'Statut fiche', 'Collecte par', 'Date collecte', 'GPS Latitude', 'GPS Longitude', 'Precision GPS (m)',
        ];
    }

    public function map($v): array
    {
        return [
            $v->vehicle_type, $v->category, $v->brand, $v->model, $v->version, $v->color,
            $v->commissioning_date?->format('d/m/Y'), $v->contract_type,
            $v->registration_number, $v->temporary_registration, $v->chassis_number,
            $v->chassis_readable !== null ? ($v->chassis_readable ? 'Oui' : 'Non') : '',
            $v->fuel_type, $v->transmission, $v->engine_displacement, $v->seats_count,
            $v->load_capacity, $v->mileage,
            $v->status, $v->structure_ci,
            $v->has_roll_bars !== null ? ($v->has_roll_bars ? 'Oui' : 'Non') : '',
            $v->special_equipment,
            $v->technical_inspection_date?->format('d/m/Y'),
            $v->is_insured !== null ? ($v->is_insured ? 'Oui' : 'Non') : '',
            $v->insurance_company, $v->policy_number, $v->coverage_type,
            $v->insurance_start_date?->format('d/m/Y'), $v->insurance_end_date?->format('d/m/Y'),
            $v->user_direction, $v->user_matricule, $v->user_driver_license,
            $v->financing_mode, $v->bank_name, $v->contract_number,
            $v->withdrawal_start_date?->format('d/m/Y'), $v->withdrawal_end_date?->format('d/m/Y'),
            $v->contract_start_date?->format('d/m/Y'), $v->provision_date?->format('d/m/Y'),
            $v->form_status, $v->collector?->full_name, $v->collected_at?->format('d/m/Y H:i'),
            $v->gps_latitude, $v->gps_longitude, $v->gps_accuracy,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
