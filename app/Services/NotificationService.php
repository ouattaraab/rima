<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use App\Models\Vehicle;

class NotificationService
{
    /**
     * Notify supervisors and admins when a vehicle is synchronized by an agent.
     *
     * @return array<Notification> Created notifications
     */
    public function notifySynchronization(Vehicle $vehicle): array
    {
        $registration = $vehicle->registration_number ?? $vehicle->temporary_registration ?? 'N/A';

        // Get the agent who synced
        $agent = $vehicle->collected_by ? User::find($vehicle->collected_by) : null;
        $agentName = $agent ? ($agent->full_name ?: $agent->username) : 'Un agent';
        $structure = $vehicle->structure_ci ?? '';

        $notifications = [];

        // Notify all supervisors (CIDEC + SODECI) and admins
        $recipients = User::whereIn('role', ['supervisor_cidec', 'supervisor_sodeci', 'admin_sodeci'])
            ->where('is_active', true)
            ->get();

        foreach ($recipients as $recipient) {
            $notifications[] = Notification::create([
                'user_id' => $recipient->id,
                'type' => 'vehicle_synchronized',
                'title' => 'Nouvelle fiche synchronisée',
                'message' => "{$agentName} a synchronisé la fiche du véhicule {$registration} ({$vehicle->brand} {$vehicle->model})." . ($structure ? " Structure : {$structure}." : ''),
                'data' => [
                    'vehicle_id' => $vehicle->id,
                    'registration_number' => $registration,
                    'brand' => $vehicle->brand,
                    'model' => $vehicle->model,
                    'agent_id' => $vehicle->collected_by,
                    'agent_name' => $agentName,
                    'structure_ci' => $structure,
                ],
            ]);
        }

        return $notifications;
    }

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
