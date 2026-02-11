<?php

namespace App\Exceptions;

use Exception;

class IncompleteFormException extends Exception
{
    public function __construct(
        public readonly array $missingFields,
        public readonly int $completionPercentage,
    ) {
        parent::__construct('Fiche incomplete. Champs manquants: ' . implode(', ', $missingFields));
    }

    public function render()
    {
        return response()->json([
            'success' => false,
            'error' => [
                'code' => 'INCOMPLETE_FORM',
                'message' => $this->getMessage(),
                'details' => [
                    'missing_fields' => $this->missingFields,
                    'completion_percentage' => $this->completionPercentage,
                ],
            ],
            'timestamp' => now()->toIso8601String(),
        ], 422);
    }
}
