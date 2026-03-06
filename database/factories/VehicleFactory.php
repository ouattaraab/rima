<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Vehicle>
 */
class VehicleFactory extends Factory
{
    protected $model = Vehicle::class;

    public function definition(): array
    {
        return [
            'vehicle_type' => 'Auto',
            'category' => 'Berline',
            'brand' => 'Toyota',
            'model' => 'Corolla',
            'version' => 'SE',
            'commissioning_date' => now()->subYears(2),
            'contract_type' => 'Flotte',
            'color' => 'Blanc',
            'registration_number' => strtoupper(fake()->unique()->bothify('??-####-??')),
            'chassis_number' => strtoupper(fake()->unique()->bothify('??????????#######')),
            'chassis_readable' => true,
            'fuel_type' => 'Essence',
            'transmission' => 'Automatique',
            'engine_displacement' => 1800,
            'seats_count' => 5,
            'mileage' => 45000,
            'status' => 'En service',
            'structure_ci' => 'AB1234',
            'technical_inspection_date' => now()->subMonths(3),
            'is_insured' => true,
            'insurance_company' => 'NSIA',
            'policy_number' => 'POL-' . fake()->numerify('######'),
            'insurance_start_date' => now()->subMonths(6),
            'insurance_end_date' => now()->addMonths(6),
            'gps_latitude' => 5.3600,
            'gps_longitude' => -4.0083,
            'gps_accuracy' => 10.5,
            'collected_at' => now(),
            'form_status' => 'draft',
            'user_direction' => 'Direction Technique',
            'user_matricule' => 'AB12345',
            'user_driver_license' => 'CI-2024-12345',
            'revision' => 1,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn () => ['form_status' => 'draft']);
    }

    public function synchronized(): static
    {
        return $this->state(fn () => ['form_status' => 'synchronized']);
    }

    public function validated(): static
    {
        return $this->state(fn () => [
            'form_status' => 'validated',
            'validated_at' => now(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn () => [
            'form_status' => 'rejected',
            'rejection_reason' => 'data_inconsistency',
            'rejection_comment' => 'Donnees incoherentes detectees lors du controle qualite.',
        ]);
    }

    public function pickup(): static
    {
        return $this->state(fn () => [
            'category' => 'Pick-up',
            'version' => null,
            'has_roll_bars' => true,
            'load_capacity' => 1500,
        ]);
    }

    public function camion(): static
    {
        return $this->state(fn () => [
            'category' => 'Camion',
            'version' => null,
            'load_capacity' => 5000,
            'special_equipment' => 'Grue hydraulique',
        ]);
    }

    public function moto(): static
    {
        return $this->state(fn () => [
            'vehicle_type' => 'Moto',
            'category' => 'Moto',
            'version' => null,
            'transmission' => null,
            'seats_count' => 2,
        ]);
    }

    public function uninsured(): static
    {
        return $this->state(fn () => [
            'is_insured' => false,
            'insurance_company' => null,
            'policy_number' => null,
            'coverage_type' => null,
            'insurance_start_date' => null,
            'insurance_end_date' => null,
            'status' => 'Reforme',
        ]);
    }
}
