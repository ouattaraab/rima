<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VehicleCoherenceTest extends TestCase
{
    use RefreshDatabase;

    private User $agent;

    protected function setUp(): void
    {
        parent::setUp();
        $this->agent = User::factory()->agentCidec()->create();
    }

    // -------------------------------------------------------------------------
    // getCoherenceErrors() tests
    // -------------------------------------------------------------------------

    public function test_coherent_vehicle_has_no_errors(): void
    {
        $vehicle = Vehicle::factory()->create(['collected_by' => $this->agent->id]);
        $this->assertEmpty($vehicle->getCoherenceErrors());
    }

    // CDC ID-05: Version uniquement pour Berline
    public function test_version_prohibited_for_non_berline(): void
    {
        $vehicle = Vehicle::factory()->pickup()->create([
            'collected_by' => $this->agent->id,
            'version' => 'LX',
        ]);
        $errors = $vehicle->getCoherenceErrors();
        $this->assertArrayHasKey('version', $errors);
    }

    public function test_version_allowed_for_berline(): void
    {
        $vehicle = Vehicle::factory()->create([
            'collected_by' => $this->agent->id,
            'category' => 'Berline',
            'version' => 'SE',
        ]);
        $errors = $vehicle->getCoherenceErrors();
        $this->assertArrayNotHasKey('version', $errors);
    }

    // CDC ID-06: Date mise en circulation <= aujourd'hui
    public function test_future_commissioning_date_is_error(): void
    {
        $vehicle = Vehicle::factory()->create([
            'collected_by' => $this->agent->id,
            'commissioning_date' => now()->addYear(),
        ]);
        $errors = $vehicle->getCoherenceErrors();
        $this->assertArrayHasKey('commissioning_date', $errors);
    }

    public function test_past_commissioning_date_is_ok(): void
    {
        $vehicle = Vehicle::factory()->create([
            'collected_by' => $this->agent->id,
            'commissioning_date' => now()->subYear(),
        ]);
        $errors = $vehicle->getCoherenceErrors();
        $this->assertArrayNotHasKey('commissioning_date', $errors);
    }

    // CDC TE-04: Nombre de places > 0
    public function test_zero_seats_is_error(): void
    {
        $vehicle = Vehicle::factory()->create([
            'collected_by' => $this->agent->id,
            'seats_count' => 0,
        ]);
        $errors = $vehicle->getCoherenceErrors();
        $this->assertArrayHasKey('seats_count', $errors);
    }

    public function test_negative_seats_is_error(): void
    {
        $vehicle = Vehicle::factory()->create([
            'collected_by' => $this->agent->id,
            'seats_count' => -1,
        ]);
        $errors = $vehicle->getCoherenceErrors();
        $this->assertArrayHasKey('seats_count', $errors);
    }

    // CDC TE-05: Charge utile > 0
    public function test_zero_load_capacity_is_error(): void
    {
        $vehicle = Vehicle::factory()->pickup()->create([
            'collected_by' => $this->agent->id,
            'load_capacity' => 0,
        ]);
        $errors = $vehicle->getCoherenceErrors();
        $this->assertArrayHasKey('load_capacity', $errors);
    }

    // CDC TE-06: Kilometrage strictement positif
    public function test_zero_mileage_is_error(): void
    {
        $vehicle = Vehicle::factory()->create([
            'collected_by' => $this->agent->id,
            'mileage' => 0,
        ]);
        $errors = $vehicle->getCoherenceErrors();
        $this->assertArrayHasKey('mileage', $errors);
    }

    public function test_positive_mileage_is_ok(): void
    {
        $vehicle = Vehicle::factory()->create([
            'collected_by' => $this->agent->id,
            'mileage' => 1,
        ]);
        $errors = $vehicle->getCoherenceErrors();
        $this->assertArrayNotHasKey('mileage', $errors);
    }

    // CDC ST-03: Equipements speciaux uniquement Camion
    public function test_special_equipment_prohibited_for_non_camion(): void
    {
        $vehicle = Vehicle::factory()->create([
            'collected_by' => $this->agent->id,
            'category' => 'Berline',
            'special_equipment' => 'Some equipment',
        ]);
        $errors = $vehicle->getCoherenceErrors();
        $this->assertArrayHasKey('special_equipment', $errors);
    }

    public function test_special_equipment_allowed_for_camion(): void
    {
        $vehicle = Vehicle::factory()->camion()->create([
            'collected_by' => $this->agent->id,
        ]);
        $errors = $vehicle->getCoherenceErrors();
        $this->assertArrayNotHasKey('special_equipment', $errors);
    }

    // CDC AS-05/AS-06: Date debut < date fin assurance
    public function test_insurance_start_after_end_is_error(): void
    {
        $vehicle = Vehicle::factory()->create([
            'collected_by' => $this->agent->id,
            'is_insured' => true,
            'insurance_start_date' => now()->addMonth(),
            'insurance_end_date' => now()->subMonth(),
        ]);
        $errors = $vehicle->getCoherenceErrors();
        $this->assertArrayHasKey('insurance_end_date', $errors);
    }

    public function test_insurance_dates_correct_order_is_ok(): void
    {
        $vehicle = Vehicle::factory()->create([
            'collected_by' => $this->agent->id,
            'is_insured' => true,
            'insurance_start_date' => now()->subMonth(),
            'insurance_end_date' => now()->addMonth(),
        ]);
        $errors = $vehicle->getCoherenceErrors();
        $this->assertArrayNotHasKey('insurance_end_date', $errors);
    }

    // CDC VT-01: Date visite technique <= aujourd'hui
    public function test_future_technical_inspection_is_error(): void
    {
        $vehicle = Vehicle::factory()->create([
            'collected_by' => $this->agent->id,
            'technical_inspection_date' => now()->addYear(),
        ]);
        $errors = $vehicle->getCoherenceErrors();
        $this->assertArrayHasKey('technical_inspection_date', $errors);
    }

    // CDC 5.7: Matricule 7 caracteres alphanumeriques
    public function test_invalid_matricule_format_is_error(): void
    {
        $vehicle = Vehicle::factory()->create([
            'collected_by' => $this->agent->id,
            'user_matricule' => 'AB123',  // seulement 5 chars
        ]);
        $errors = $vehicle->getCoherenceErrors();
        $this->assertArrayHasKey('user_matricule', $errors);
    }

    public function test_valid_matricule_format_is_ok(): void
    {
        $vehicle = Vehicle::factory()->create([
            'collected_by' => $this->agent->id,
            'user_matricule' => 'AB12345',
        ]);
        $errors = $vehicle->getCoherenceErrors();
        $this->assertArrayNotHasKey('user_matricule', $errors);
    }

    // -------------------------------------------------------------------------
    // getMissingFields() tests
    // -------------------------------------------------------------------------

    public function test_complete_vehicle_has_no_missing_fields(): void
    {
        $vehicle = Vehicle::factory()->create(['collected_by' => $this->agent->id]);

        // Create photos
        $vehicle->photos()->createMany([
            ['photo_type' => 'front', 'file_path' => 'front.jpg', 'captured_at' => now()],
            ['photo_type' => 'rear', 'file_path' => 'rear.jpg', 'captured_at' => now()],
            ['photo_type' => 'side', 'file_path' => 'side.jpg', 'captured_at' => now()],
        ]);

        $this->assertEmpty($vehicle->getMissingFields());
    }

    public function test_missing_registration_detected(): void
    {
        $vehicle = Vehicle::factory()->create([
            'collected_by' => $this->agent->id,
            'registration_number' => null,
            'temporary_registration' => null,
        ]);
        $this->assertContains('registration_number', $vehicle->getMissingFields());
    }

    public function test_temporary_registration_satisfies_requirement(): void
    {
        $vehicle = Vehicle::factory()->create([
            'collected_by' => $this->agent->id,
            'registration_number' => null,
            'temporary_registration' => 'TMP-1234',
        ]);
        $missing = $vehicle->getMissingFields();
        $this->assertNotContains('registration_number', $missing);
    }

    public function test_transmission_required_for_auto(): void
    {
        $vehicle = Vehicle::factory()->create([
            'collected_by' => $this->agent->id,
            'vehicle_type' => 'Auto',
            'transmission' => null,
        ]);
        $this->assertContains('transmission', $vehicle->getMissingFields());
    }

    public function test_transmission_not_required_for_moto(): void
    {
        $vehicle = Vehicle::factory()->moto()->create([
            'collected_by' => $this->agent->id,
        ]);
        // Create photos
        $vehicle->photos()->createMany([
            ['photo_type' => 'front', 'file_path' => 'f.jpg', 'captured_at' => now()],
            ['photo_type' => 'rear', 'file_path' => 'r.jpg', 'captured_at' => now()],
            ['photo_type' => 'side', 'file_path' => 's.jpg', 'captured_at' => now()],
        ]);
        $this->assertNotContains('transmission', $vehicle->getMissingFields());
    }

    public function test_structure_ci_required_when_en_service(): void
    {
        $vehicle = Vehicle::factory()->create([
            'collected_by' => $this->agent->id,
            'status' => 'En service',
            'structure_ci' => null,
        ]);
        $this->assertContains('structure_ci', $vehicle->getMissingFields());
    }

    public function test_insurance_required_when_en_service(): void
    {
        $vehicle = Vehicle::factory()->create([
            'collected_by' => $this->agent->id,
            'status' => 'En service',
            'is_insured' => false,
        ]);
        $this->assertContains('is_insured', $vehicle->getMissingFields());
    }

    public function test_insurance_details_required_when_insured(): void
    {
        $vehicle = Vehicle::factory()->create([
            'collected_by' => $this->agent->id,
            'is_insured' => true,
            'insurance_company' => null,
            'policy_number' => null,
            'insurance_start_date' => null,
            'insurance_end_date' => null,
        ]);
        $missing = $vehicle->getMissingFields();
        $this->assertContains('insurance_company', $missing);
        $this->assertContains('policy_number', $missing);
        $this->assertContains('insurance_start_date', $missing);
        $this->assertContains('insurance_end_date', $missing);
    }

    public function test_roll_bars_required_for_pickup(): void
    {
        $vehicle = Vehicle::factory()->create([
            'collected_by' => $this->agent->id,
            'category' => 'Pick-up',
            'has_roll_bars' => null,
            'load_capacity' => 1500,
        ]);
        $this->assertContains('has_roll_bars', $vehicle->getMissingFields());
    }

    public function test_load_capacity_required_for_camion(): void
    {
        $vehicle = Vehicle::factory()->create([
            'collected_by' => $this->agent->id,
            'category' => 'Camion',
            'version' => null,
            'load_capacity' => null,
        ]);
        $this->assertContains('load_capacity', $vehicle->getMissingFields());
    }

    public function test_photos_required(): void
    {
        $vehicle = Vehicle::factory()->create(['collected_by' => $this->agent->id]);
        // No photos created
        $missing = $vehicle->getMissingFields();
        $this->assertContains('photo_front', $missing);
        $this->assertContains('photo_rear', $missing);
        $this->assertContains('photo_side', $missing);
    }

    public function test_chassis_photo_required_when_not_readable(): void
    {
        $vehicle = Vehicle::factory()->create([
            'collected_by' => $this->agent->id,
            'chassis_readable' => false,
        ]);
        $this->assertContains('photo_chassis', $vehicle->getMissingFields());
    }

    // -------------------------------------------------------------------------
    // Completion percentage
    // -------------------------------------------------------------------------

    public function test_completion_100_when_all_fields_and_photos(): void
    {
        $vehicle = Vehicle::factory()->create(['collected_by' => $this->agent->id]);
        $vehicle->photos()->createMany([
            ['photo_type' => 'front', 'file_path' => 'f.jpg', 'captured_at' => now()],
            ['photo_type' => 'rear', 'file_path' => 'r.jpg', 'captured_at' => now()],
            ['photo_type' => 'side', 'file_path' => 's.jpg', 'captured_at' => now()],
        ]);
        $this->assertEquals(100, $vehicle->completion_percentage);
    }

    public function test_completion_below_100_when_missing_fields(): void
    {
        $vehicle = Vehicle::factory()->create([
            'collected_by' => $this->agent->id,
            'brand' => '',
            'model' => '',
        ]);
        $this->assertLessThan(100, $vehicle->completion_percentage);
    }
}
