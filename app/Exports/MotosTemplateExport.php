<?php

namespace App\Exports;

use App\Models\Brand;
use App\Models\InsuranceCompany;
use App\Models\Structure;
use App\Models\VehicleCategory;
use App\Models\VehicleModel;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class MotosTemplateExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithTitle, WithEvents
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
            'structure',
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
                'Yamaha',
                'YBR125E',
                'Noir',
                '125',
                'Essence',
                '',
                '2',
                '',
                '5000',
                'En service',
                'DRA - Direction Regionale Abidjan',
                '',
                '01/01/2020',
                'Flotte',
                '15/01/2025',
                'Oui',
                'SUNU Assurances',
                'POL123',
                '01/01/2025',
                '31/12/2025',
                'AB12345',
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 18, 'B' => 22, 'C' => 22, 'D' => 14,
            'E' => 18, 'F' => 22, 'G' => 12, 'H' => 12,
            'I' => 14, 'J' => 16, 'K' => 10, 'L' => 14,
            'M' => 14, 'N' => 16, 'O' => 38, 'P' => 20,
            'Q' => 22, 'R' => 16, 'S' => 22, 'T' => 10,
            'U' => 24, 'V' => 16, 'W' => 16, 'X' => 16,
            'Y' => 16,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $lastCol = 'Y';

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

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $maxRow = 500;

                // ── Données dynamiques depuis la BDD ──
                $categories = VehicleCategory::where('is_active', true)->orderBy('name')->pluck('name')->toArray();
                $brands = Brand::where('is_active', true)->orderBy('name')->pluck('name')->toArray();
                $models = VehicleModel::where('is_active', true)->orderBy('name')->pluck('name')->toArray();
                $structures = Structure::where('is_active', true)->orderBy('code')
                    ->get()
                    ->map(fn($s) => $s->code . ' - ' . $s->name)
                    ->toArray();
                $insurances = InsuranceCompany::where('is_active', true)->orderBy('name')->pluck('name')->toArray();

                // ── Données fixes ──
                $colors = ['Blanc', 'Noir', 'Gris', 'Bleu', 'Rouge', 'Vert', 'Jaune', 'Beige', 'Marron', 'Autre'];
                $fuelTypes = ['Essence', 'Gasoil', 'Hybride', 'Electrique'];
                $transmissions = ['Automatique', 'Manuelle'];
                $statuses = ['En service', 'En reparation', 'Reforme', 'Cede'];
                $contractTypes = ['Sous contrat', 'Flotte'];
                $yesNo = ['Oui', 'Non'];

                // ── Feuille cachée "Referentiels" pour les listes longues ──
                $refSheet = $event->sheet->getParent()->createSheet();
                $refSheet->setTitle('Referentiels');

                $refColumns = [
                    'A' => ['header' => 'Marques', 'data' => $brands],
                    'B' => ['header' => 'Modeles', 'data' => $models],
                    'C' => ['header' => 'Structures', 'data' => $structures],
                    'D' => ['header' => 'Assurances', 'data' => $insurances],
                    'E' => ['header' => 'Categories', 'data' => $categories],
                ];

                foreach ($refColumns as $col => $info) {
                    $refSheet->setCellValue("{$col}1", $info['header']);
                    foreach ($info['data'] as $i => $value) {
                        $refSheet->setCellValue("{$col}" . ($i + 2), $value);
                    }
                }

                $refSheet->setSheetState(Worksheet::SHEETSTATE_HIDDEN);

                // ── Dropdowns inline (listes courtes ≤ 255 chars) ──
                $inlineDropdowns = [
                    'G' => $colors,
                    'I' => $fuelTypes,
                    'J' => $transmissions,
                    'N' => $statuses,
                    'R' => $contractTypes,
                    'T' => $yesNo,
                ];

                foreach ($inlineDropdowns as $col => $values) {
                    $validation = new DataValidation();
                    $validation->setType(DataValidation::TYPE_LIST);
                    $validation->setFormula1('"' . implode(',', $values) . '"');
                    $validation->setAllowBlank(true);
                    $validation->setShowDropDown(true);
                    $validation->setShowErrorMessage(true);
                    $validation->setErrorStyle(DataValidation::STYLE_STOP);
                    $validation->setErrorTitle('Valeur invalide');
                    $validation->setError('Veuillez selectionner une valeur dans la liste.');

                    for ($row = 2; $row <= $maxRow; $row++) {
                        $sheet->getCell("{$col}{$row}")->setDataValidation(clone $validation);
                    }
                }

                // ── Dropdowns via feuille cachée (listes longues/dynamiques) ──
                $refDropdowns = [
                    'D' => ['col' => 'E', 'count' => count($categories)],   // categorie
                    'E' => ['col' => 'A', 'count' => count($brands)],       // marque
                    'F' => ['col' => 'B', 'count' => count($models)],       // modele
                    'O' => ['col' => 'C', 'count' => count($structures)],   // structure
                    'U' => ['col' => 'D', 'count' => count($insurances)],   // compagnie_assurance
                ];

                foreach ($refDropdowns as $templateCol => $ref) {
                    if ($ref['count'] === 0) {
                        continue;
                    }

                    $lastRow = $ref['count'] + 1;
                    $formula = "Referentiels!\${$ref['col']}\$2:\${$ref['col']}\${$lastRow}";

                    $validation = new DataValidation();
                    $validation->setType(DataValidation::TYPE_LIST);
                    $validation->setFormula1($formula);
                    $validation->setAllowBlank(true);
                    $validation->setShowDropDown(true);
                    $validation->setShowErrorMessage(true);
                    $validation->setErrorStyle(DataValidation::STYLE_STOP);
                    $validation->setErrorTitle('Valeur invalide');
                    $validation->setError('Veuillez selectionner une valeur dans la liste.');

                    for ($row = 2; $row <= $maxRow; $row++) {
                        $sheet->getCell("{$templateCol}{$row}")->setDataValidation(clone $validation);
                    }
                }

                // Re-focus on the main sheet
                $event->sheet->getParent()->setActiveSheetIndex(0);
            },
        ];
    }
}
