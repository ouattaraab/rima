<?php

namespace App\Services;

use App\Models\Vehicle;
use App\Models\VehicleHistory;
use Illuminate\Support\Facades\Log;

class VehicleService
{
    public function __construct(
        private AuditService $auditService,
        private NotificationService $notificationService,
    ) {}

    public function recordHistory(
        Vehicle $vehicle,
        string $action,
        string $changedBy,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $comment = null,
    ): VehicleHistory {
        return VehicleHistory::create([
            'vehicle_id' => $vehicle->id,
            'action' => $action,
            'changed_by' => $changedBy,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'comment' => $comment,
        ]);
    }

    public function syncVehicle(Vehicle $vehicle, string $userId): Vehicle
    {
        $missing = $vehicle->getMissingFields();
        if (!empty($missing)) {
            throw new \App\Exceptions\IncompleteFormException($missing, $vehicle->completion_percentage);
        }

        // CDC: Verification des regles de coherence avant synchronisation
        $coherenceErrors = $vehicle->getCoherenceErrors();
        if (!empty($coherenceErrors)) {
            throw new \App\Exceptions\CoherenceException($coherenceErrors);
        }

        $vehicle->update([
            'form_status' => 'synchronized',
        ]);

        $vehicle->increment('revision');

        $this->recordHistory($vehicle, 'synchronized', $userId, comment: 'Fiche synchronisee');

        // Notify supervisors and admins (non-critical: must not break sync)
        try {
            $this->notificationService->notifySynchronization($vehicle);
        } catch (\Exception $e) {
            Log::warning('Notification failed during sync', [
                'vehicle_id' => $vehicle->id,
                'error' => $e->getMessage(),
            ]);
        }

        return $vehicle->fresh();
    }

    public function validateVehicle(Vehicle $vehicle, string $userId, ?string $comment = null): Vehicle
    {
        $vehicle->update([
            'form_status' => 'validated',
            'validated_by' => $userId,
            'validated_at' => now(),
        ]);

        $vehicle->increment('revision');

        $this->recordHistory($vehicle, 'validated', $userId, comment: $comment);

        try {
            $this->notificationService->notifyValidation($vehicle);
        } catch (\Exception $e) {
            Log::warning('Notification failed during validation', [
                'vehicle_id' => $vehicle->id,
                'error' => $e->getMessage(),
            ]);
        }

        return $vehicle->fresh();
    }

    public function rejectVehicle(Vehicle $vehicle, string $userId, string $reason, ?string $comment = null): Vehicle
    {
        $vehicle->update([
            'form_status' => 'rejected',
            'validated_by' => $userId,
            'validated_at' => now(),
            'rejection_reason' => $reason,
            'rejection_comment' => $comment,
        ]);

        $vehicle->increment('revision');

        $this->recordHistory($vehicle, 'rejected', $userId, comment: $comment ?? $reason);

        try {
            $this->notificationService->notifyRejection($vehicle, $reason, $comment);
        } catch (\Exception $e) {
            Log::warning('Notification failed during rejection', [
                'vehicle_id' => $vehicle->id,
                'error' => $e->getMessage(),
            ]);
        }

        return $vehicle->fresh();
    }
}
