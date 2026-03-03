<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class MotosTemplateExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    public function title(): string
    {
        return 'Template Motos';
    }

    public function headings(): array
    {
        return [
            'immatriculation',
            'immatriculation_provisoire',
            'n_chassis',
            'categorie',
            'marque',
            'modele',
            'couleur',
            'cylindree',
            'carburant',
            'transmission',
            'places',
            'charge_utile',
            'kilometrage',
            'statut',
            'structure_ci',
            'equipements_speciaux',
            'date_mise_en_circulation',
            'type_contrat',
            'date_controle_technique',
            'assure',
            'compagnie_assurance',
            'numero_police',
            'debut_assurance',
            'fin_assurance',
            'matricule_agent',
            'direction',
        ];
    }

    public function array(): array
    {
        return [
            [
                'AB 1234 CI',
                '',
                'JH2MC1300EK000001',
                'Moto',
                'YAMAHA',
                'YBR 125',
                'Noir',
                '125',
                'Essence',
                '',
                '2',
                '',
                '5000',
                'En service',
                '0200',
                '',
                '01/01/2020',
                'Flotte',
                '15/01/2025',
                'Oui',
                'SUNU',
                'POL123',
                '01/01/2025',
                '31/12/2025',
                'AB12345',
                'DGA',
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 18, 'B' => 22, 'C' => 22, 'D' => 12,
            'E' => 14, 'F' => 14, 'G' => 12, 'H' => 12,
            'I' => 12, 'J' => 14, 'K' => 10, 'L' => 14,
            'M' => 14, 'N' => 14, 'O' => 14, 'P' => 20,
            'Q' => 22, 'R' => 14, 'S' => 22, 'T' => 10,
            'U' => 22, 'V' => 16, 'W' => 16, 'X' => 16,
            'Y' => 16, 'Z' => 12,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $lastCol = 'Z';

        // Header row styling
        $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10, 'name' => 'Calibri'],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2DB56B']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '229E56']],
            ],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(28);

        // Example row styling (light green background)
        $sheet->getStyle("A2:{$lastCol}2")->applyFromArray([
            'font' => ['italic' => true, 'color' => ['rgb' => '64748B'], 'size' => 10, 'name' => 'Calibri'],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F0FDF4']],
        ]);

        return [];
    }
}
