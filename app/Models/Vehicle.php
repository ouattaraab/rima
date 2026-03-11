<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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
        'fuel_type', 'transmission', 'engine_displacement', 'fiscal_power',
        'seats_count', 'load_capacity', 'mileage',
        // Statut
        'status', 'structure_ci', 'has_roll_bars', 'cabin_type', 'special_equipment',
        // Visite technique
        'technical_inspection_date',
        // Assurance
        'is_insured', 'insurance_company', 'policy_number',
        'insurance_start_date', 'insurance_end_date',
        // GPS
        'gps_latitude', 'gps_longitude', 'gps_accuracy',
        // Conducteur
        'driver_not_assigned',
        // Metadata
        'collected_at', 'collection_completed_at', 'collected_by', 'form_status',
        // @deprecated — Ancien format single-driver (V1.4). Utiliser la table vehicle_drivers.
        'user_direction', 'user_matricule', 'user_driver_license',
        // Validation
        'validated_by', 'validated_at', 'rejection_reason', 'rejection_comment',
        // Donnees post-inventaires (Section 5.9 - V1.4)
        'financing_mode', 'bank_name', 'contract_number',
        'withdrawal_start_date', 'withdrawal_end_date',
        'contract_start_date', 'provision_date',
        'code_immo', 'code_equipement',
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
            'collection_completed_at' => 'datetime',
            'validated_at' => 'datetime',
            'driver_not_assigned' => 'boolean',
            'is_insured' => 'boolean',
            'chassis_readable' => 'boolean',
            'has_roll_bars' => 'boolean',
            'gps_latitude' => 'decimal:8',
            'gps_longitude' => 'decimal:8',
            'gps_accuracy' => 'decimal:2',
            'seats_count' => 'integer',
            'mileage' => 'integer',
            'engine_displacement' => 'integer',
            'fiscal_power' => 'integer',
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

    public function drivers(): HasMany
    {
        return $this->hasMany(VehicleDriver::class)->orderBy('position');
    }

    public function primaryDriver(): HasOne
    {
        return $this->hasOne(VehicleDriver::class)->where('is_primary', true);
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

    /**
     * Returns true if the vehicle can be edited (draft or rejected).
     */
    public function isEditable(): bool
    {
        return in_array($this->form_status, ['draft', 'rejected']);
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
        ];

        $missing = [];
        foreach ($required as $field) {
            if (is_null($this->$field) || $this->$field === '') {
                $missing[] = $field;
            }
        }

        // Au moins un conducteur (sauf si non affecte)
        if (!$this->driver_not_assigned && $this->drivers()->count() === 0) {
            $missing[] = 'drivers';
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

        // Type de cabine obligatoire si Pick-up
        if ($this->category === 'Pick-up' && empty($this->cabin_type)) {
            $missing[] = 'cabin_type';
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

        // Photos obligatoires
        $photoTypes = $photoTypes ?? $this->photos()->pluck('photo_type')->toArray();
        if ($this->vehicle_type === 'Moto') {
            // Moto : 1 seule photo suffit (n'importe quel type)
            if (count($photoTypes) === 0) {
                $missing[] = 'photo';
            }
        } else {
            // Auto : 3 photos obligatoires (front, rear, side)
            if (!in_array('front', $photoTypes)) $missing[] = 'photo_front';
            if (!in_array('rear', $photoTypes)) $missing[] = 'photo_rear';
            if (!in_array('side', $photoTypes)) $missing[] = 'photo_side';
        }

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

        // Type de cabine uniquement Pick-up
        if (!empty($this->cabin_type) && $this->category !== 'Pick-up') {
            $errors['cabin_type'] = 'Le type de cabine ne concerne que les Pick-up.';
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

        // Cross-field: visite technique >= mise en circulation
        if ($this->technical_inspection_date && $this->commissioning_date
            && $this->technical_inspection_date->lessThan($this->commissioning_date)) {
            $errors['technical_inspection_date'] = 'La date de controle technique ne peut pas etre anterieure a la mise en circulation.';
        }

        // Cross-field: assurance debut >= mise en circulation
        if ($this->insurance_start_date && $this->commissioning_date
            && $this->insurance_start_date->lessThan($this->commissioning_date)) {
            $errors['insurance_start_date'] = 'La date de debut d\'assurance ne peut pas etre anterieure a la mise en circulation.';
        }

        // CDC 5.7: Matricule exactement 7 caracteres alphanumeriques (multi-conducteurs)
        foreach ($this->drivers as $i => $driver) {
            if (!empty($driver->matricule) && !preg_match('/^[A-Z0-9]{7}$/i', $driver->matricule)) {
                $errors["driver_{$i}_matricule"] = "Conducteur " . ($i + 1) . ": le matricule doit comporter exactement 7 caracteres alphanumeriques.";
            }
        }

        return $errors;
    }

    /**
     * DBCG completion: code_equipement + code_immo required.
     * If contract_type is "Sous contrat", also require contract_start_date + provision_date.
     */
    public function getIsDbcgCompleteAttribute(): bool
    {
        if (empty($this->code_equipement) || empty($this->code_immo)) {
            return false;
        }
        if ($this->contract_type === 'Sous contrat') {
            return !empty($this->contract_start_date) && !empty($this->provision_date);
        }
        return true;
    }

    /**
     * DFC completion: financing_mode + code_immo required.
     * If financing_mode is "Leasing", also require bank_name + contract_number + withdrawal dates.
     */
    public function getIsDfcCompleteAttribute(): bool
    {
        if (empty($this->financing_mode) || empty($this->code_immo)) {
            return false;
        }
        if ($this->financing_mode === 'Leasing') {
            return !empty($this->bank_name)
                && !empty($this->contract_number)
                && !empty($this->withdrawal_start_date)
                && !empty($this->withdrawal_end_date);
        }
        return true;
    }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->form_status) {
            'draft' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">Brouillon</span>',
            'synchronized' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">Synchronisée</span>',
            'validated' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">Validée</span>',
            'rejected' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-rose-100 text-rose-700">Rejetée</span>',
            default => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">-</span>',
        };
    }

    public function getVehicleStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'En service' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">En service</span>',
            'En reparation' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">En réparation</span>',
            'Reforme' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">Réformé</span>',
            'Cede' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">Cédé</span>',
            default => '',
        };
    }

    public function getCompletionPercentageAttribute(): int
    {
        // Calcul dynamique du total requis selon le type/categorie/statut
        $total = 14; // 13 champs toujours requis + 1 "has drivers"
        $total += ($this->vehicle_type === 'Moto') ? 1 : 3; // Moto=1 photo, Auto=3 photos
        $total += 1; // au moins une immatriculation

        if ($this->vehicle_type === 'Auto') $total++; // transmission
        if (in_array($this->category, ['Camion', 'Pick-up'])) $total++; // load_capacity
        if ($this->category === 'Pick-up') $total += 2; // has_roll_bars + cabin_type
        if (in_array($this->status, ['En service', 'En reparation'])) $total++; // structure_ci
        if ($this->status === 'En service') $total++; // is_insured
        if ($this->is_insured) $total += 4; // insurance_company, policy, start, end
        if ($this->chassis_readable === false) $total++; // photo_chassis

        $missingCount = count($this->getMissingFields());
        $filled = max(0, $total - $missingCount);
        return (int) min(100, round(($filled / max(1, $total)) * 100));
    }
}
