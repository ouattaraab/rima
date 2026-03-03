<?php

namespace App\Exports;

use App\Models\Vehicle;
use Barryvdh\DomPDF\Facade\Pdf;

class VehiclePdfExport
{
    /**
     * Generate a PDF for a single vehicle sheet (fiche vehicule).
     */
    public static function generate(Vehicle $vehicle)
    {
        $vehicle->load(['photos', 'collector', 'validator']);

        $data = [
            'vehicle' => $vehicle,
            'generatedAt' => now()->format('d/m/Y H:i'),
        ];

        $filename = 'PRIMA_FICHE_'
            . ($vehicle->registration_number ?? 'SANS_IMMAT')
            . '_' . now()->format('Ymd') . '.pdf';

        return Pdf::loadView('exports.vehicle-pdf', $data)
            ->setPaper('a4', 'portrait')
            ->download($filename);
    }

    /**
     * Generate a PDF list of multiple vehicles.
     */
    public static function generateList($vehicles, array $filters = [])
    {
        $data = [
            'vehicles' => $vehicles,
            'filters' => $filters,
            'generatedAt' => now()->format('d/m/Y H:i'),
            'total' => $vehicles->count(),
        ];

        $filename = 'PRIMA_LISTE_' . now()->format('Ymd') . '.pdf';

        return Pdf::loadView('exports.vehicles-list-pdf', $data)
            ->setPaper('a4', 'landscape')
            ->download($filename);
    }
}
