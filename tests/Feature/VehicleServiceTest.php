<?php

namespace Tests\Feature;

use App\Exceptions\CoherenceException;
use App\Exceptions\IncompleteFormException;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\AuditService;
use App\Services\NotificationService;
use App\Services\VehicleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VehicleServiceTest extends TestCase
{
    use RefreshDatabase;

    private User $agent;
    private User $supervisor;
    private VehicleService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->agent = User::factory()->agentCidec()->create();
        $this->supervisor = User::factory()->supervisorSodeci()->create();

        $this->service = new VehicleService(
            app(AuditService::class),
            app(NotificationService::class),
        );
    }

    private function createCompleteVehicle(array $overrides = []): Vehicle
    {
        $vehicle = Vehicle::factory()->create(array_merge([
            'collected_by' => $this->agent->id,
            'form_status' => 'draft',
        ], $overrides));

        // Add required photos
        $vehicle->photos()->createMany([
            ['photo_type' => 'front', 'file_path' => 'front.jpg', 'captured_at' => now()],
            ['photo_type' => 'rear', 'file_path' => 'rear.jpg', 'captured_at' => now()],
            ['photo_type' => 'side', 'file_path' => 'side.jpg', 'captured_at' => now()],
        ]);

        return $vehicle;
    }

    // -------------------------------------------------------------------------
    // syncVehicle
    // -------------------------------------------------------------------------

    public function test_sync_complete_vehicle_succeeds(): void
    {
        $vehicle = $this->createCompleteVehicle();

        $result = $this->service->syncVehicle($vehicle, $this->agent->id);

        $this->assertEquals('synchronized', $result->form_status);
    }

    public function test_sync_creates_history(): void
    {
        $vehicle = $this->createCompleteVehicle();

        $this->service->syncVehicle($vehicle, $this->agent->id);

        $this->assertDatabaseHas('vehicle_histories', [
            'vehicle_id' => $vehicle->id,
            'action' => 'synchronized',
            'changed_by' => $this->agent->id,
        ]);
    }

    public function test_sync_increments_revision(): void
    {
        $vehicle = $this->createCompleteVehicle(['revision' => 1]);

        $result = $this->service->syncVehicle($vehicle, $this->agent->id);

        $this->assertEquals(2, $result->revision);
    }

    public function test_sync_fails_with_missing_fields(): void
    {
        $vehicle = Vehicle::factory()->create([
            'collected_by' => $this->agent->id,
            'form_status' => 'draft',
            'brand' => '',
        ]);

        $this->expectException(IncompleteFormException::class);
        $this->service->syncVehicle($vehicle, $this->agent->id);
    }

    public function test_sync_fails_with_coherence_errors(): void
    {
        // Vehicle with future commissioning date (coherence error)
        $vehicle = $this->createCompleteVehicle([
            'commissioning_date' => now()->addYear(),
        ]);

        $this->expectException(CoherenceException::class);
        $this->service->syncVehicle($vehicle, $this->agent->id);
    }

    public function test_sync_fails_with_zero_mileage(): void
    {
        $vehicle = $this->createCompleteVehicle([
            'mileage' => 0,
        ]);

        // Either IncompleteFormException (from getMissingFields check for mileage > 0)
        // or CoherenceException (from getCoherenceErrors)
        $this->expectException(\Exception::class);
        $this->service->syncVehicle($vehicle, $this->agent->id);
    }

    public function test_sync_fails_with_insurance_dates_incoherent(): void
    {
        $vehicle = $this->createCompleteVehicle([
            'insurance_start_date' => now()->addMonth(),
            'insurance_end_date' => now()->subMonth(),
        ]);

        $this->expectException(CoherenceException::class);
        $this->service->syncVehicle($vehicle, $this->agent->id);
    }

    // -------------------------------------------------------------------------
    // validateVehicle
    // -------------------------------------------------------------------------

    public function test_validate_vehicle_succeeds(): void
    {
        $vehicle = $this->createCompleteVehicle(['form_status' => 'synchronized']);

        $result = $this->service->validateVehicle($vehicle, $this->supervisor->id, 'Fiche conforme');

        $this->assertEquals('validated', $result->form_status);
        $this->assertEquals($this->supervisor->id, $result->validated_by);
        $this->assertNotNull($result->validated_at);
    }

    public function test_validate_creates_history(): void
    {
        $vehicle = $this->createCompleteVehicle(['form_status' => 'synchronized']);

        $this->service->validateVehicle($vehicle, $this->supervisor->id);

        $this->assertDatabaseHas('vehicle_histories', [
            'vehicle_id' => $vehicle->id,
            'action' => 'validated',
        ]);
    }

    // -------------------------------------------------------------------------
    // rejectVehicle
    // -------------------------------------------------------------------------

    public function test_reject_vehicle_with_reason(): void
    {
        $vehicle = $this->createCompleteVehicle(['form_status' => 'synchronized']);

        $result = $this->service->rejectVehicle(
            $vehicle,
            $this->supervisor->id,
            'data_inconsistency',
            'Le kilometrage semble incorrect pour ce type de vehicule.'
        );

        $this->assertEquals('rejected', $result->form_status);
        $this->assertEquals('data_inconsistency', $result->rejection_reason);
        $this->assertNotNull($result->rejection_comment);
    }

    public function test_reject_creates_history(): void
    {
        $vehicle = $this->createCompleteVehicle(['form_status' => 'synchronized']);

        $this->service->rejectVehicle(
            $vehicle, $this->supervisor->id,
            'photo_issue', 'Photos floues'
        );

        $this->assertDatabaseHas('vehicle_histories', [
            'vehicle_id' => $vehicle->id,
            'action' => 'rejected',
        ]);
    }
}
