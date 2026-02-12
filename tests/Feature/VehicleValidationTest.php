<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VehicleValidationTest extends TestCase
{
    use RefreshDatabase;

    private User $agent;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->agent = User::factory()->agentCidec()->create();
        $this->token = $this->agent->createToken('test')->plainTextToken;
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'vehicle_type' => 'Auto',
            'category' => 'Berline',
            'brand' => 'Toyota',
            'model' => 'Corolla',
            'version' => 'SE',
            'commissioning_date' => now()->subYear()->format('Y-m-d'),
            'contract_type' => 'Flotte',
            'color' => 'Blanc',
            'registration_number' => 'AB-1234-CD',
            'chassis_number' => 'ABCDE12345678901',
            'chassis_readable' => true,
            'fuel_type' => 'Essence',
            'transmission' => 'Automatique',
            'engine_displacement' => 1800,
            'seats_count' => 5,
            'mileage' => 45000,
            'status' => 'En service',
            'structure_ci' => 'AB1234',
            'technical_inspection_date' => now()->subMonth()->format('Y-m-d'),
            'is_insured' => true,
            'insurance_company' => 'NSIA',
            'policy_number' => 'POL-123456',
            'coverage_type' => 'Tous risques',
            'insurance_start_date' => now()->subMonths(6)->format('Y-m-d'),
            'insurance_end_date' => now()->addMonths(6)->format('Y-m-d'),
            'user_direction' => 'Direction Technique',
            'user_matricule' => 'AB12345',
            'user_driver_license' => 'CI-2024-12345',
        ], $overrides);
    }

    // -------------------------------------------------------------------------
    // Store validation
    // -------------------------------------------------------------------------

    public function test_store_valid_vehicle_succeeds(): void
    {
        $response = $this->withToken($this->token)
            ->postJson('/api/v1/vehicles', $this->validPayload());

        $response->assertStatus(201);
    }

    // CDC ID-05: Version prohibee si non Berline
    public function test_store_rejects_version_for_non_berline(): void
    {
        $response = $this->withToken($this->token)
            ->postJson('/api/v1/vehicles', $this->validPayload([
                'category' => 'Utilitaire',
                'version' => 'LX',
            ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['version']);
    }

    // CDC ID-06: Date mise en circulation <= aujourd'hui
    public function test_store_rejects_future_commissioning_date(): void
    {
        $response = $this->withToken($this->token)
            ->postJson('/api/v1/vehicles', $this->validPayload([
                'commissioning_date' => now()->addYear()->format('Y-m-d'),
            ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['commissioning_date']);
    }

    // CDC TE-04: Nombre de places > 0
    public function test_store_rejects_zero_seats(): void
    {
        $response = $this->withToken($this->token)
            ->postJson('/api/v1/vehicles', $this->validPayload([
                'seats_count' => 0,
            ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['seats_count']);
    }

    // CDC TE-06: Kilometrage strictement positif
    public function test_store_rejects_zero_mileage(): void
    {
        $response = $this->withToken($this->token)
            ->postJson('/api/v1/vehicles', $this->validPayload([
                'mileage' => 0,
            ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['mileage']);
    }

    // CDC: Immatriculation unique ou temporaire requise
    public function test_store_requires_at_least_one_registration(): void
    {
        $response = $this->withToken($this->token)
            ->postJson('/api/v1/vehicles', $this->validPayload([
                'registration_number' => null,
                'temporary_registration' => null,
            ]));

        $response->assertStatus(422);
    }

    // CDC: Temporary registration accepted instead of definitive
    public function test_store_accepts_temporary_registration(): void
    {
        $response = $this->withToken($this->token)
            ->postJson('/api/v1/vehicles', $this->validPayload([
                'registration_number' => null,
                'temporary_registration' => 'TMP-1234',
            ]));

        $response->assertStatus(201);
    }

    // CDC: Transmission obligatoire pour Auto
    public function test_store_requires_transmission_for_auto(): void
    {
        $response = $this->withToken($this->token)
            ->postJson('/api/v1/vehicles', $this->validPayload([
                'vehicle_type' => 'Auto',
                'transmission' => null,
            ]));

        $response->assertStatus(422);
    }

    // CDC: Structure/CI obligatoire si En service
    public function test_store_requires_structure_ci_when_en_service(): void
    {
        $response = $this->withToken($this->token)
            ->postJson('/api/v1/vehicles', $this->validPayload([
                'status' => 'En service',
                'structure_ci' => null,
            ]));

        $response->assertStatus(422);
    }

    // CDC: Charge utile obligatoire pour Camion
    public function test_store_requires_load_capacity_for_camion(): void
    {
        $response = $this->withToken($this->token)
            ->postJson('/api/v1/vehicles', $this->validPayload([
                'category' => 'Camion',
                'version' => null,
                'load_capacity' => null,
            ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['load_capacity']);
    }

    // CDC: Arceaux obligatoires pour Pick-up
    public function test_store_requires_has_roll_bars_for_pickup(): void
    {
        $response = $this->withToken($this->token)
            ->postJson('/api/v1/vehicles', $this->validPayload([
                'category' => 'Pick-up',
                'version' => null,
                'has_roll_bars' => null,
                'load_capacity' => 1500,
            ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['has_roll_bars']);
    }

    // CDC AS-05/AS-06: Date fin assurance > date debut
    public function test_store_rejects_insurance_end_before_start(): void
    {
        $response = $this->withToken($this->token)
            ->postJson('/api/v1/vehicles', $this->validPayload([
                'insurance_start_date' => now()->format('Y-m-d'),
                'insurance_end_date' => now()->subMonth()->format('Y-m-d'),
            ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['insurance_end_date']);
    }

    // CDC: Assurance obligatoire si En service
    public function test_store_requires_insurance_when_en_service(): void
    {
        $response = $this->withToken($this->token)
            ->postJson('/api/v1/vehicles', $this->validPayload([
                'status' => 'En service',
                'is_insured' => false,
            ]));

        $response->assertStatus(422);
    }

    // CDC 5.7: Matricule exactement 7 caracteres
    public function test_store_rejects_invalid_matricule_length(): void
    {
        $response = $this->withToken($this->token)
            ->postJson('/api/v1/vehicles', $this->validPayload([
                'user_matricule' => 'AB123',
            ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['user_matricule']);
    }

    // CDC: Equipements speciaux uniquement Camion
    public function test_store_rejects_special_equipment_for_non_camion(): void
    {
        $response = $this->withToken($this->token)
            ->postJson('/api/v1/vehicles', $this->validPayload([
                'category' => 'Berline',
                'special_equipment' => 'Something',
            ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['special_equipment']);
    }

    // CDC: Couleur limitee
    public function test_store_rejects_invalid_color(): void
    {
        $response = $this->withToken($this->token)
            ->postJson('/api/v1/vehicles', $this->validPayload([
                'color' => 'Violet',
            ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['color']);
    }

    // CDC: Chassis regex
    public function test_store_rejects_invalid_chassis_format(): void
    {
        $response = $this->withToken($this->token)
            ->postJson('/api/v1/vehicles', $this->validPayload([
                'chassis_number' => 'abc-123!@#',
            ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['chassis_number']);
    }

    // CDC: Immatriculation unique
    public function test_store_rejects_duplicate_registration(): void
    {
        Vehicle::factory()->create([
            'collected_by' => $this->agent->id,
            'registration_number' => 'AB-1234-CD',
        ]);

        $response = $this->withToken($this->token)
            ->postJson('/api/v1/vehicles', $this->validPayload([
                'registration_number' => 'AB-1234-CD',
            ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['registration_number']);
    }
}
