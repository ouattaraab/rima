<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        // Identification
        'vehicle_type', 'category', 'brand', 'model', 'version',
        'commissioning_date', 'contract_type', 'color',
        // Immatriculation
        'registration_number', 'temporary_registration',
        'chassis_number', 'chassis_readable',
        // Technique
        'fuel_type', 'transmission', 'engine_displacement',
        'seats_count', 'load_capacity', 'mileage',
        // Statut
        'status', 'structure_ci', 'has_roll_bars', 'special_equipment',
        // Visite technique
        'technical_inspection_date',
        // Assurance
        'is_insured', 'insurance_company', 'policy_number',
        'coverage_type', 'insurance_start_date', 'insurance_end_date',
        // GPS
        'gps_latitude', 'gps_longitude', 'gps_accuracy',
        // Metadata
        'collected_at', 'collected_by', 'form_status',
        // Identification utilisateur (Section 5.7 - V1.4)
        'user_direction', 'user_matricule', 'user_driver_license',
        // Validation
        'validated_by', 'validated_at', 'rejection_reason', 'rejection_comment',
        // Donnees post-inventaires (Section 5.9 - V1.4)
        'financing_mode', 'bank_name', 'contract_number',
        'withdrawal_start_date', 'withdrawal_end_date',
        'contract_start_date', 'provision_date',
        'revision',
    ];

    protected function casts(): array
    {
        return [
            'commissioning_date' => 'date',
            'technical_inspection_date' => 'date',
            'insurance_start_date' => 'date',
            'insurance_end_date' => 'date',
            'withdrawal_start_date' => 'date',
            'withdrawal_end_date' => 'date',
            'contract_start_date' => 'date',
            'provision_date' => 'date',
            'collected_at' => 'datetime',
            'validated_at' => 'datetime',
            'is_insured' => 'boolean',
            'chassis_readable' => 'boolean',
            'has_roll_bars' => 'boolean',
            'gps_latitude' => 'decimal:8',
            'gps_longitude' => 'decimal:8',
            'gps_accuracy' => 'decimal:2',
            'seats_count' => 'integer',
            'mileage' => 'integer',
            'engine_displacement' => 'integer',
            'load_capacity' => 'integer',
            'revision' => 'integer',
        ];
    }

    // Relations
    public function collector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'collected_by');
    }

    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(VehiclePhoto::class);
    }

    public function histories(): HasMany
    {
        return $this->hasMany(VehicleHistory::class)->orderByDesc('created_at');
    }

    // Scopes
    public function scopeDraft($query)
    {
        return $query->where('form_status', 'draft');
    }

    public function scopeSynchronized($query)
    {
        return $query->where('form_status', 'synchronized');
    }

    public function scopeValidated($query)
    {
        return $query->where('form_status', 'validated');
    }

    public function scopeRejected($query)
    {
        return $query->where('form_status', 'rejected');
    }

    // Helpers
    public function isDraft(): bool
    {
        return $this->form_status === 'draft';
    }

    public function isSynchronized(): bool
    {
        return $this->form_status === 'synchronized';
    }

    public function getMissingFields(): array
    {
        $required = [
            'vehicle_type', 'category', 'brand', 'model',
            'commissioning_date', 'contract_type', 'color',
            'chassis_readable', 'fuel_type', 'seats_count', 'mileage',
            'status', 'technical_inspection_date',
            // Section 5.7 - V1.4 : Identification utilisateur
            'user_direction', 'user_matricule', 'user_driver_license',
        ];

        $missing = [];
        foreach ($required as $field) {
            if (is_null($this->$field) || $this->$field === '') {
                $missing[] = $field;
            }
        }

        // Kilometrage > 0
        if (!is_null($this->mileage) && $this->mileage <= 0) {
            $missing[] = 'mileage';
        }

        // Au moins une immatriculation
        if (is_null($this->registration_number) && is_null($this->temporary_registration)) {
            $missing[] = 'registration_number';
        }

        // Arceaux obligatoires si Pick-up
        if ($this->category === 'Pick-up' && is_null($this->has_roll_bars)) {
            $missing[] = 'has_roll_bars';
        }

        // Charge utile obligatoire si Camion ou Pick-up
        if (in_array($this->category, ['Camion', 'Pick-up']) && (is_null($this->load_capacity) || $this->load_capacity <= 0)) {
            $missing[] = 'load_capacity';
        }

        // Structure/CI obligatoire si En service ou En reparation
        if (in_array($this->status, ['En service', 'En reparation']) && empty($this->structure_ci)) {
            $missing[] = 'structure_ci';
        }

        // Transmission obligatoire si Auto (pas Moto)
        if ($this->vehicle_type === 'Auto' && is_null($this->transmission)) {
            $missing[] = 'transmission';
        }

        // Assurance obligatoire si En service (CDC 5.5)
        if ($this->status === 'En service' && !$this->is_insured) {
            $missing[] = 'is_insured';
        }

        // Details assurance si assure
        if ($this->is_insured) {
            if (empty($this->insurance_company)) $missing[] = 'insurance_company';
            if (empty($this->policy_number)) $missing[] = 'policy_number';
            if (empty($this->insurance_start_date)) $missing[] = 'insurance_start_date';
            if (empty($this->insurance_end_date)) $missing[] = 'insurance_end_date';
        }

        // Photo justificative si chassis non lisible
        if ($this->chassis_readable === false) {
            $photoTypes = $this->photos()->pluck('photo_type')->toArray();
            if (!in_array('chassis', $photoTypes)) {
                $missing[] = 'photo_chassis';
            }
        }

        // Photos obligatoires (3 photos minimum)
        $photoTypes = $photoTypes ?? $this->photos()->pluck('photo_type')->toArray();
        if (!in_array('front', $photoTypes)) $missing[] = 'photo_front';
        if (!in_array('rear', $photoTypes)) $missing[] = 'photo_rear';
        if (!in_array('side', $photoTypes)) $missing[] = 'photo_side';

        return $missing;
    }

    /**
     * CDC Coherence rules: returns field → error message for logical inconsistencies.
     */
    public function getCoherenceErrors(): array
    {
        $errors = [];

        // CDC ID-05: Version uniquement pour Berline
        if (!empty($this->version) && $this->category !== 'Berline') {
            $errors['version'] = 'La version ne concerne que les Berlines.';
        }

        // CDC ID-06: Date mise en circulation <= aujourd'hui
        if ($this->commissioning_date && $this->commissioning_date->isFuture()) {
            $errors['commissioning_date'] = 'La date de mise en circulation ne peut pas etre dans le futur.';
        }

        // CDC TE-04: Nombre de places > 0
        if ($this->seats_count !== null && $this->seats_count <= 0) {
            $errors['seats_count'] = 'Le nombre de places doit etre superieur a 0.';
        }

        // CDC TE-05: Charge utile > 0 si renseignee
        if ($this->load_capacity !== null && $this->load_capacity <= 0) {
            $errors['load_capacity'] = 'La charge utile doit etre superieure a 0.';
        }

        // CDC TE-06: Kilometrage strictement positif
        if ($this->mileage !== null && $this->mileage <= 0) {
            $errors['mileage'] = 'Le kilometrage doit etre strictement positif.';
        }

        // CDC TE-02: Transmission interdite pour les Motos
        if (!empty($this->transmission) && $this->vehicle_type === 'Moto') {
            $errors['transmission'] = 'La transmission n\'est pas applicable pour les Motos.';
        }

        // CDC ST-03: Equipements speciaux uniquement Camion
        if (!empty($this->special_equipment) && $this->category !== 'Camion') {
            $errors['special_equipment'] = 'Les equipements speciaux ne concernent que les Camions.';
        }

        // CDC AS-05/AS-06: Date debut assurance < date fin assurance
        if ($this->insurance_start_date && $this->insurance_end_date
            && $this->insurance_start_date->greaterThanOrEqualTo($this->insurance_end_date)) {
            $errors['insurance_end_date'] = 'La date de fin d\'assurance doit etre posterieure a la date de debut.';
        }

        // CDC VT-01: Date visite technique <= aujourd'hui
        if ($this->technical_inspection_date && $this->technical_inspection_date->isFuture()) {
            $errors['technical_inspection_date'] = 'La date de controle technique ne peut pas etre dans le futur.';
        }

        // CDC 5.7: Matricule exactement 7 caracteres alphanumeriques
        if (!empty($this->user_matricule) && !preg_match('/^[A-Z0-9]{7}$/i', $this->user_matricule)) {
            $errors['user_matricule'] = 'Le matricule doit comporter exactement 7 caracteres alphanumeriques.';
        }

        return $errors;
    }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->form_status) {
            'draft' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">Brouillon</span>',
            'synchronized' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">Synchronisee</span>',
            'validated' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">Validee</span>',
            'rejected' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-rose-100 text-rose-700">Rejetee</span>',
            default => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">-</span>',
        };
    }

    public function getVehicleStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'En service' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">En service</span>',
            'En reparation' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">En reparation</span>',
            'Reforme' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">Reforme</span>',
            'Cede' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">Cede</span>',
            default => '',
        };
    }

    public function getCompletionPercentageAttribute(): int
    {
        // Calcul dynamique du total requis selon le type/categorie/statut
        $total = 17; // champs toujours requis + immatriculation + 3 photos
        $total += 3; // photo_front, photo_rear, photo_side
        $total += 1; // au moins une immatriculation

        if ($this->vehicle_type === 'Auto') $total++; // transmission
        if (in_array($this->category, ['Camion', 'Pick-up'])) $total++; // load_capacity
        if ($this->category === 'Pick-up') $total++; // has_roll_bars
        if (in_array($this->status, ['En service', 'En reparation'])) $total++; // structure_ci
        if ($this->status === 'En service') $total++; // is_insured
        if ($this->is_insured) $total += 4; // insurance_company, policy, start, end
        if ($this->chassis_readable === false) $total++; // photo_chassis

        $missingCount = count($this->getMissingFields());
        $filled = max(0, $total - $missingCount);
        return (int) min(100, round(($filled / max(1, $total)) * 100));
    }
}
