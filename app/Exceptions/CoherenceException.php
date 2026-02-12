<?php

namespace App\Exceptions;

use Exception;

class CoherenceException extends Exception
{
    public function __construct(
        public readonly array $coherenceErrors,
    ) {
        parent::__construct('Incoherences detectees: ' . implode('; ', array_values($coherenceErrors)));
    }

    public function render()
    {
        return response()->json([
            'success' => false,
            'error' => [
                'code' => 'COHERENCE_ERROR',
                'message' => 'Des incoherences ont ete detectees dans la fiche.',
                'details' => [
                    'errors' => $this->coherenceErrors,
                ],
            ],
            'timestamp' => now()->toIso8601String(),
        ], 422);
    }
}
