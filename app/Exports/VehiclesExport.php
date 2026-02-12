<?php

namespace App\Exports;

use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;

class VehiclesExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithEvents, WithColumnWidths
{
    protected array $filters;

    /** Section header column indices (1-based) for color grouping */
    private const SECTION_IDENTIFICATION = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];               // Cols A-H
    private const SECTION_IMMATRICULATION = ['I', 'J', 'K', 'L'];                                    // Cols I-L
    private const SECTION_TECHNIQUE       = ['M', 'N', 'O', 'P', 'Q', 'R'];                          // Cols M-R
    private const SECTION_STATUT          = ['S', 'T', 'U', 'V'];                                    // Cols S-V
    private const SECTION_REGLEMENTAIRE   = ['W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC'];                 // Cols W-AC
    private const SECTION_UTILISATEUR     = ['AD', 'AE', 'AF'];                                      // Cols AD-AF
    private const SECTION_FINANCIER       = ['AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM'];             // Cols AG-AM
    private const SECTION_METADONNEES     = ['AN', 'AO', 'AP', 'AQ', 'AR', 'AS'];                    // Cols AN-AS

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function query(): Builder
    {
        $query = Vehicle::with('collector');

        if (!empty($this->filters['form_status'])) {
            $query->where('form_status', $this->filters['form_status']);
        }
        if (!empty($this->filters['brand'])) {
            $query->where('brand', $this->filters['brand']);
        }
        if (!empty($this->filters['vehicle_type'])) {
            $query->where('vehicle_type', $this->filters['vehicle_type']);
        }
        if (!empty($this->filters['category'])) {
            $query->where('category', $this->filters['category']);
        }
        if (!empty($this->filters['vehicle_status'])) {
            $query->where('status', $this->filters['vehicle_status']);
        }
        if (!empty($this->filters['date_from'])) {
            $query->whereDate('collected_at', '>=', $this->filters['date_from']);
        }
        if (!empty($this->filters['date_to'])) {
            $query->whereDate('collected_at', '<=', $this->filters['date_to']);
        }

        return $query->orderByDesc('collected_at');
    }

    public function headings(): array
    {
        return [
            // ── Identification (A-H) ──
            'Type', 'Categorie', 'Marque', 'Modele', 'Version', 'Couleur',
            'Date mise en circulation', 'Type contrat',
            // ── Immatriculation (I-L) ──
            'Immatriculation', 'Immat. provisoire', 'N° Chassis', 'Chassis lisible',
            // ── Technique (M-R) ──
            'Carburant', 'Transmission', 'Cylindree', 'Nb places', 'Charge utile (kg)', 'Kilometrage',
            // ── Statut (S-V) ──
            'Statut vehicule', 'Structure/CI', 'Arceaux', 'Equipements speciaux',
            // ── Reglementaire (W-AC) ──
            'Date controle technique',
            'Assure', 'Compagnie assurance', 'N° police', 'Type couverture',
            'Debut assurance', 'Fin assurance',
            // ── Utilisateur (AD-AF) ──
            'Direction', 'Matricule utilisateur', 'N° Permis',
            // ── Financier (AG-AM) ──
            'Mode financement', 'Banque', 'N° contrat', 'Debut prelevement',
            'Fin prelevement', 'Debut contrat', 'Date mise a disposition',
            // ── Metadonnees (AN-AS) ──
            'Statut fiche', 'Collecte par', 'Date collecte', 'GPS Latitude', 'GPS Longitude', 'Precision GPS (m)',
        ];
    }

    public function map($v): array
    {
        $formStatusFr = match ($v->form_status) {
            'validated'    => 'Valide',
            'synchronized' => 'Synchronise',
            'rejected'     => 'Rejete',
            'draft'        => 'Brouillon',
            default        => $v->form_status,
        };

        return [
            $v->vehicle_type, $v->category, $v->brand, $v->model, $v->version, $v->color,
            $v->commissioning_date?->format('d/m/Y'), $v->contract_type,
            $v->registration_number, $v->temporary_registration, $v->chassis_number,
            $v->chassis_readable !== null ? ($v->chassis_readable ? 'Oui' : 'Non') : '',
            $v->fuel_type, $v->transmission, $v->engine_displacement, $v->seats_count,
            $v->load_capacity, $v->mileage,
            $v->status, $v->structure_ci,
            $v->has_roll_bars !== null ? ($v->has_roll_bars ? 'Oui' : 'Non') : '',
            $v->special_equipment,
            $v->technical_inspection_date?->format('d/m/Y'),
            $v->is_insured !== null ? ($v->is_insured ? 'Oui' : 'Non') : '',
            $v->insurance_company, $v->policy_number, $v->coverage_type,
            $v->insurance_start_date?->format('d/m/Y'), $v->insurance_end_date?->format('d/m/Y'),
            $v->user_direction, $v->user_matricule, $v->user_driver_license,
            $v->financing_mode, $v->bank_name, $v->contract_number,
            $v->withdrawal_start_date?->format('d/m/Y'), $v->withdrawal_end_date?->format('d/m/Y'),
            $v->contract_start_date?->format('d/m/Y'), $v->provision_date?->format('d/m/Y'),
            $formStatusFr, $v->collector?->full_name, $v->collected_at?->format('d/m/Y H:i'),
            $v->gps_latitude, $v->gps_longitude, $v->gps_accuracy,
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 14, 'B' => 14, 'C' => 14, 'D' => 14, 'E' => 12, 'F' => 11,
            'G' => 18, 'H' => 14,
            'I' => 17, 'J' => 17, 'K' => 22, 'L' => 13,
            'M' => 12, 'N' => 14, 'O' => 12, 'P' => 10, 'Q' => 15, 'R' => 13,
            'S' => 15, 'T' => 14, 'U' => 10, 'V' => 20,
            'W' => 18, 'X' => 10, 'Y' => 20, 'Z' => 14, 'AA' => 15, 'AB' => 14, 'AC' => 14,
            'AD' => 16, 'AE' => 18, 'AF' => 16,
            'AG' => 16, 'AH' => 14, 'AI' => 14, 'AJ' => 16, 'AK' => 16, 'AL' => 14, 'AM' => 18,
            'AN' => 13, 'AO' => 18, 'AP' => 16, 'AQ' => 13, 'AR' => 13, 'AS' => 15,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $lastCol = 'AS';
        $lastRow = $sheet->getHighestRow();

        // ── Global font ──
        $sheet->getParent()->getDefaultStyle()->getFont()
            ->setName('Calibri')
            ->setSize(10);

        // ── Freeze first 6 columns + header row ──
        $sheet->freezePane('G2');

        // ── Auto-filter ──
        $sheet->setAutoFilter("A1:{$lastCol}1");

        // ── Print settings ──
        $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
        $sheet->getPageSetup()->setFitToWidth(1);
        $sheet->getPageSetup()->setFitToHeight(0);

        return [
            // ── Header row (row 1) ──
            1 => [
                'font' => [
                    'bold'  => true,
                    'size'  => 10,
                    'color' => ['rgb' => '1E293B'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical'   => Alignment::VERTICAL_CENTER,
                    'wrapText'   => true,
                ],
                'borders' => [
                    'bottom' => [
                        'borderStyle' => Border::BORDER_MEDIUM,
                        'color'       => ['rgb' => '94A3B8'],
                    ],
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet   = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();
                $lastCol = 'AS';

                // ──────────────────────────────────────
                // Section color groups for header bg
                // ──────────────────────────────────────
                $sectionColors = [
                    ['cols' => self::SECTION_IDENTIFICATION, 'color' => 'E8F5E9'],   // Vert très clair
                    ['cols' => self::SECTION_IMMATRICULATION, 'color' => 'E3F2FD'],  // Bleu très clair
                    ['cols' => self::SECTION_TECHNIQUE,       'color' => 'FFF3E0'],  // Orange très clair
                    ['cols' => self::SECTION_STATUT,          'color' => 'F3E5F5'],  // Violet très clair
                    ['cols' => self::SECTION_REGLEMENTAIRE,   'color' => 'E0F7FA'],  // Cyan très clair
                    ['cols' => self::SECTION_UTILISATEUR,     'color' => 'FBE9E7'],  // Rouge très clair
                    ['cols' => self::SECTION_FINANCIER,       'color' => 'FFFDE7'],  // Jaune très clair
                    ['cols' => self::SECTION_METADONNEES,     'color' => 'ECEFF1'],  // Gris très clair
                ];

                foreach ($sectionColors as $section) {
                    foreach ($section['cols'] as $col) {
                        $sheet->getStyle("{$col}1")->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setRGB($section['color']);
                    }
                }

                // ──────────────────────────────────────
                // Row height for header
                // ──────────────────────────────────────
                $sheet->getRowDimension(1)->setRowHeight(30);

                // ──────────────────────────────────────
                // Data rows styling (2 -> lastRow)
                // ──────────────────────────────────────
                if ($lastRow >= 2) {
                    // Vertical alignment
                    $sheet->getStyle("A2:{$lastCol}{$lastRow}")
                        ->getAlignment()
                        ->setVertical(Alignment::VERTICAL_CENTER);

                    // Light horizontal borders between rows
                    $sheet->getStyle("A2:{$lastCol}{$lastRow}")
                        ->getBorders()
                        ->getBottom()
                        ->setBorderStyle(Border::BORDER_THIN)
                        ->setColor(new Color('E2E8F0'));

                    // Vertical borders between columns (inner only)
                    $sheet->getStyle("A1:{$lastCol}{$lastRow}")
                        ->getBorders()
                        ->getVertical()
                        ->setBorderStyle(Border::BORDER_THIN)
                        ->setColor(new Color('E2E8F0'));

                    // ── Zebra striping ──
                    for ($row = 2; $row <= $lastRow; $row++) {
                        if ($row % 2 === 0) {
                            $sheet->getStyle("A{$row}:{$lastCol}{$row}")
                                ->getFill()
                                ->setFillType(Fill::FILL_SOLID)
                                ->getStartColor()->setRGB('F8FAFC');
                        }
                    }

                    // ── Frozen columns (A-F) subtle background ──
                    $sheet->getStyle("A2:F{$lastRow}")
                        ->getFont()
                        ->setBold(false);

                    // Bold immatriculation column (I)
                    $sheet->getStyle("I2:I{$lastRow}")
                        ->getFont()
                        ->setBold(true)
                        ->getColor()->setRGB('0F172A');

                    // ── Statut fiche column (AN) - color coding ──
                    for ($row = 2; $row <= $lastRow; $row++) {
                        $status = $sheet->getCell("AN{$row}")->getValue();
                        $statusColors = match ($status) {
                            'Valide'      => ['166534', 'DCFCE7'],
                            'Synchronise' => ['92400E', 'FEF3C7'],
                            'Rejete'      => ['991B1B', 'FEE2E2'],
                            'Brouillon'   => ['475569', 'F1F5F9'],
                            default       => ['475569', 'F1F5F9'],
                        };
                        $sheet->getStyle("AN{$row}")
                            ->getFont()->getColor()->setRGB($statusColors[0]);
                        $sheet->getStyle("AN{$row}")
                            ->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setRGB($statusColors[1]);
                        $sheet->getStyle("AN{$row}")
                            ->getFont()->setBold(true);
                        $sheet->getStyle("AN{$row}")
                            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    }

                    // ── Number formatting for kilometrage (R) ──
                    $sheet->getStyle("R2:R{$lastRow}")
                        ->getNumberFormat()
                        ->setFormatCode('#,##0');

                    $sheet->getStyle("R2:R{$lastRow}")
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                    // ── GPS precision (AS) ──
                    $sheet->getStyle("AS2:AS{$lastRow}")
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                }

                // ──────────────────────────────────────
                // Section separator lines (thicker borders)
                // ──────────────────────────────────────
                $sectionStarts = ['I', 'M', 'S', 'W', 'AD', 'AG', 'AN'];
                foreach ($sectionStarts as $col) {
                    $sheet->getStyle("{$col}1:{$col}{$lastRow}")
                        ->getBorders()
                        ->getLeft()
                        ->setBorderStyle(Border::BORDER_MEDIUM)
                        ->setColor(new Color('CBD5E1'));
                }

                // ──────────────────────────────────────
                // Remove outer left and right borders
                // ──────────────────────────────────────
                $sheet->getStyle("A1:A{$lastRow}")
                    ->getBorders()
                    ->getLeft()
                    ->setBorderStyle(Border::BORDER_NONE);

                $sheet->getStyle("{$lastCol}1:{$lastCol}{$lastRow}")
                    ->getBorders()
                    ->getRight()
                    ->setBorderStyle(Border::BORDER_NONE);
            },
        ];
    }
}
