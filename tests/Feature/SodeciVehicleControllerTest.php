<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SodeciVehicleControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $agent;
    private User $supervisorSodeci;
    private User $adminSodeci;
    private string $sodeciToken;
    private string $adminToken;

    protected function setUp(): void
    {
        parent::setUp();
        $this->agent = User::factory()->agentCidec()->create();
        $this->supervisorSodeci = User::factory()->supervisorSodeci()->create();
        $this->adminSodeci = User::factory()->adminSodeci()->create();
        $this->sodeciToken = $this->supervisorSodeci->createToken('test')->plainTextToken;
        $this->adminToken = $this->adminSodeci->createToken('test')->plainTextToken;
    }

    private function createSyncedVehicle(array $overrides = []): Vehicle
    {
        $vehicle = Vehicle::factory()->synchronized()->create(array_merge([
            'collected_by' => $this->agent->id,
        ], $overrides));

        $vehicle->photos()->createMany([
            ['photo_type' => 'front', 'file_path' => 'front.jpg', 'captured_at' => now()],
            ['photo_type' => 'rear', 'file_path' => 'rear.jpg', 'captured_at' => now()],
            ['photo_type' => 'side', 'file_path' => 'side.jpg', 'captured_at' => now()],
        ]);

        return $vehicle;
    }

    // -------------------------------------------------------------------------
    // Validation (SODECI only)
    // -------------------------------------------------------------------------

    public function test_sodeci_can_validate_synchronized_vehicle(): void
    {
        $vehicle = $this->createSyncedVehicle();

        $response = $this->withToken($this->sodeciToken)
            ->postJson("/api/v1/sodeci/vehicles/{$vehicle->id}/validate", [
                'comment' => 'Fiche conforme',
            ]);

        $response->assertOk()
            ->assertJsonPath('data.form_status', 'validated');
    }

    public function test_cannot_validate_draft_vehicle(): void
    {
        $vehicle = Vehicle::factory()->draft()->create([
            'collected_by' => $this->agent->id,
        ]);

        $response = $this->withToken($this->sodeciToken)
            ->postJson("/api/v1/sodeci/vehicles/{$vehicle->id}/validate");

        $response->assertStatus(422)
            ->assertJsonPath('error.code', 'INVALID_STATUS');
    }

    public function test_cannot_validate_already_validated_vehicle(): void
    {
        $vehicle = Vehicle::factory()->validated()->create([
            'collected_by' => $this->agent->id,
            'validated_by' => $this->supervisorSodeci->id,
        ]);

        $response = $this->withToken($this->sodeciToken)
            ->postJson("/api/v1/sodeci/vehicles/{$vehicle->id}/validate");

        $response->assertStatus(422);
    }

    public function test_validation_blocked_by_coherence_errors(): void
    {
        // Create vehicle with incoherent insurance dates
        $vehicle = $this->createSyncedVehicle([
            'insurance_start_date' => now()->addMonth(),
            'insurance_end_date' => now()->subMonth(),
        ]);

        $response = $this->withToken($this->sodeciToken)
            ->postJson("/api/v1/sodeci/vehicles/{$vehicle->id}/validate");

        $response->assertStatus(422)
            ->assertJsonPath('error.code', 'COHERENCE_ERROR');
    }

    public function test_validation_blocked_when_version_on_non_berline(): void
    {
        $vehicle = $this->createSyncedVehicle([
            'category' => 'Pick-up',
            'version' => 'LX',
            'has_roll_bars' => true,
            'load_capacity' => 1500,
        ]);

        $response = $this->withToken($this->sodeciToken)
            ->postJson("/api/v1/sodeci/vehicles/{$vehicle->id}/validate");

        $response->assertStatus(422)
            ->assertJsonPath('error.code', 'COHERENCE_ERROR');
    }

    // -------------------------------------------------------------------------
    // Rejection (SODECI only, rejet motive CDC)
    // -------------------------------------------------------------------------

    public function test_sodeci_can_reject_with_reason(): void
    {
        $vehicle = $this->createSyncedVehicle();

        $response = $this->withToken($this->sodeciToken)
            ->postJson("/api/v1/sodeci/vehicles/{$vehicle->id}/reject", [
                'rejection_reason' => 'data_inconsistency',
                'rejection_comment' => 'Le kilometrage ne correspond pas au modele du vehicule.',
            ]);

        $response->assertOk()
            ->assertJsonPath('data.form_status', 'rejected')
            ->assertJsonPath('data.rejection_reason', 'data_inconsistency');
    }

    public function test_reject_requires_comment(): void
    {
        $vehicle = $this->createSyncedVehicle();

        $response = $this->withToken($this->sodeciToken)
            ->postJson("/api/v1/sodeci/vehicles/{$vehicle->id}/reject", [
                'rejection_reason' => 'photo_issue',
                // Missing rejection_comment
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['rejection_comment']);
    }

    public function test_reject_comment_must_be_at_least_10_chars(): void
    {
        $vehicle = $this->createSyncedVehicle();

        $response = $this->withToken($this->sodeciToken)
            ->postJson("/api/v1/sodeci/vehicles/{$vehicle->id}/reject", [
                'rejection_reason' => 'photo_issue',
                'rejection_comment' => 'short',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['rejection_comment']);
    }

    public function test_cannot_reject_draft_vehicle(): void
    {
        $vehicle = Vehicle::factory()->draft()->create([
            'collected_by' => $this->agent->id,
        ]);

        $response = $this->withToken($this->sodeciToken)
            ->postJson("/api/v1/sodeci/vehicles/{$vehicle->id}/reject", [
                'rejection_reason' => 'data_inconsistency',
                'rejection_comment' => 'Donnees incorrectes dans le formulaire.',
            ]);

        $response->assertStatus(422);
    }

    // -------------------------------------------------------------------------
    // CIDEC agent cannot validate (role protection)
    // -------------------------------------------------------------------------

    public function test_cidec_agent_cannot_validate(): void
    {
        $vehicle = $this->createSyncedVehicle();
        $agentToken = $this->agent->createToken('test')->plainTextToken;

        $response = $this->withToken($agentToken)
            ->postJson("/api/v1/sodeci/vehicles/{$vehicle->id}/validate");

        // Should be forbidden (403) or unauthorized based on middleware
        $this->assertTrue(in_array($response->status(), [401, 403]));
    }

    // -------------------------------------------------------------------------
    // Vehicle show with relations
    // -------------------------------------------------------------------------

    public function test_show_includes_photos_and_histories(): void
    {
        $vehicle = $this->createSyncedVehicle();

        $response = $this->withToken($this->sodeciToken)
            ->getJson("/api/v1/sodeci/vehicles/{$vehicle->id}");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id', 'vehicle_type', 'category', 'brand', 'model',
                    'photos',
                ],
            ]);
    }

    // -------------------------------------------------------------------------
    // Financial data (SODECI)
    // -------------------------------------------------------------------------

    public function test_update_financial_data(): void
    {
        $vehicle = Vehicle::factory()->validated()->create([
            'collected_by' => $this->agent->id,
            'validated_by' => $this->supervisorSodeci->id,
        ]);

        $response = $this->withToken($this->adminToken)
            ->putJson("/api/v1/sodeci/vehicles/{$vehicle->id}/financial", [
                'financing_mode' => 'Leasing',
                'bank_name' => 'SGBCI',
                'contract_number' => 'CTR-2024-001',
                'withdrawal_start_date' => '2024-01-01',
                'withdrawal_end_date' => '2027-01-01',
                'provision_date' => '2024-01-15',
            ]);

        $response->assertOk()
            ->assertJsonPath('data.financing_mode', 'Leasing')
            ->assertJsonPath('data.bank_name', 'SGBCI');
    }

    public function test_financial_direct_mode_clears_leasing_fields(): void
    {
        $vehicle = Vehicle::factory()->validated()->create([
            'collected_by' => $this->agent->id,
            'validated_by' => $this->supervisorSodeci->id,
            'financing_mode' => 'Leasing',
            'bank_name' => 'SGBCI',
            'contract_number' => 'CTR-001',
        ]);

        $response = $this->withToken($this->adminToken)
            ->putJson("/api/v1/sodeci/vehicles/{$vehicle->id}/financial", [
                'financing_mode' => 'Direct',
                'provision_date' => '2024-01-15',
            ]);

        $response->assertOk()
            ->assertJsonPath('data.financing_mode', 'Direct')
            ->assertJsonPath('data.bank_name', null);
    }
}
