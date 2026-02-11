<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\Vehicle;

class NotificationService
{
    public function notifyValidation(Vehicle $vehicle): ?Notification
    {
        if (!$vehicle->collected_by) {
            return null;
        }

        $registration = $vehicle->registration_number ?? $vehicle->temporary_registration ?? 'N/A';

        return Notification::create([
            'user_id' => $vehicle->collected_by,
            'type' => 'vehicle_validated',
            'title' => 'Fiche vehicule validee',
            'message' => "La fiche du vehicule {$registration} ({$vehicle->brand} {$vehicle->model}) a ete validee.",
            'data' => [
                'vehicle_id' => $vehicle->id,
                'registration_number' => $registration,
                'brand' => $vehicle->brand,
                'model' => $vehicle->model,
            ],
        ]);
    }

    public function notifyRejection(Vehicle $vehicle, string $reason, ?string $comment = null): ?Notification
    {
        if (!$vehicle->collected_by) {
            return null;
        }

        $registration = $vehicle->registration_number ?? $vehicle->temporary_registration ?? 'N/A';

        $message = "La fiche du vehicule {$registration} ({$vehicle->brand} {$vehicle->model}) a ete rejetee. Motif : {$reason}";
        if ($comment) {
            $message .= ". Commentaire : {$comment}";
        }

        return Notification::create([
            'user_id' => $vehicle->collected_by,
            'type' => 'vehicle_rejected',
            'title' => 'Fiche vehicule rejetee',
            'message' => $message,
            'data' => [
                'vehicle_id' => $vehicle->id,
                'registration_number' => $registration,
                'brand' => $vehicle->brand,
                'model' => $vehicle->model,
                'reason' => $reason,
                'comment' => $comment,
            ],
        ]);
    }
}
