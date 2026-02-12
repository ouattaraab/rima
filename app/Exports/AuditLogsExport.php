<?php

namespace App\Exports;

use App\Models\AuditLog;
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

class AuditLogsExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithEvents, WithColumnWidths
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function query(): Builder
    {
        $query = AuditLog::with('user');

        if (!empty($this->filters['action'])) {
            $query->where('action', $this->filters['action']);
        }
        if (!empty($this->filters['entity_type'])) {
            $query->where('entity_type', $this->filters['entity_type']);
        }
        if (!empty($this->filters['user_id'])) {
            $query->where('user_id', $this->filters['user_id']);
        }
        if (!empty($this->filters['date_from'])) {
            $query->whereDate('created_at', '>=', $this->filters['date_from']);
        }
        if (!empty($this->filters['date_to'])) {
            $query->whereDate('created_at', '<=', $this->filters['date_to']);
        }
        if (!empty($this->filters['source'])) {
            $query->where('source', $this->filters['source']);
        }

        return $query->orderByDesc('created_at');
    }

    public function headings(): array
    {
        return [
            'Date',
            'Heure',
            'Utilisateur',
            'Role',
            'Source',
            'Action',
            'Type entite',
            'ID entite',
            'Adresse IP',
            'Donnees',
        ];
    }

    public function map($log): array
    {
        return [
            $log->created_at->format('d/m/Y'),
            $log->created_at->format('H:i:s'),
            $log->user->full_name ?? '---',
            $log->user->role ?? '',
            $log->source ?? '---',
            $log->action,
            $log->entity_type ?? '',
            $log->entity_id ?? '',
            $log->ip_address ?? '',
            $log->request_body ? json_encode($log->request_body, JSON_UNESCAPED_UNICODE) : '',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 13,  // Date
            'B' => 10,  // Heure
            'C' => 22,  // Utilisateur
            'D' => 14,  // Role
            'E' => 12,  // Source
            'F' => 20,  // Action
            'G' => 16,  // Type entite
            'H' => 14,  // ID entite
            'I' => 16,  // Adresse IP
            'J' => 50,  // Donnees
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $lastCol = 'J';
        $lastRow = $sheet->getHighestRow();

        // ── Global font ──
        $sheet->getParent()->getDefaultStyle()->getFont()
            ->setName('Calibri')
            ->setSize(10);

        // ── Freeze first 3 columns (Date, Heure, Utilisateur) + header row ──
        $sheet->freezePane('D2');

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
                $lastCol = 'J';

                // ──────────────────────────────────────
                // Section color groups for header bg
                // ──────────────────────────────────────
                $sectionColors = [
                    ['cols' => ['A', 'B'],           'color' => 'E8F5E9'],  // Date/Heure → Vert clair
                    ['cols' => ['C', 'D'],           'color' => 'E3F2FD'],  // Utilisateur/Role → Bleu clair
                    ['cols' => ['E'],                 'color' => 'FFFDE7'],  // Source → Jaune clair
                    ['cols' => ['F', 'G', 'H'],      'color' => 'F3E5F5'],  // Action/Entite → Violet clair
                    ['cols' => ['I'],                 'color' => 'FFF3E0'],  // IP → Orange clair
                    ['cols' => ['J'],                 'color' => 'ECEFF1'],  // Donnees → Gris clair
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
                // Data rows styling
                // ──────────────────────────────────────
                if ($lastRow >= 2) {
                    // Vertical alignment
                    $sheet->getStyle("A2:{$lastCol}{$lastRow}")
                        ->getAlignment()
                        ->setVertical(Alignment::VERTICAL_CENTER)
                        ->setWrapText(false);

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

                    // ── Section separators (thicker vertical borders) ──
                    $sectionStarts = ['C', 'E', 'F', 'I', 'J'];
                    foreach ($sectionStarts as $col) {
                        $sheet->getStyle("{$col}1:{$col}{$lastRow}")
                            ->getBorders()
                            ->getLeft()
                            ->setBorderStyle(Border::BORDER_MEDIUM)
                            ->setColor(new Color('CBD5E1'));
                    }

                    // ── Date column (A) → gris discret ──
                    $sheet->getStyle("A2:A{$lastRow}")
                        ->getFont()->getColor()->setRGB('64748B');

                    // ── Heure column (B) → gris discret ──
                    $sheet->getStyle("B2:B{$lastRow}")
                        ->getFont()->getColor()->setRGB('64748B');

                    // ── Utilisateur column (C) → bold ──
                    $sheet->getStyle("C2:C{$lastRow}")
                        ->getFont()->setBold(true)
                        ->getColor()->setRGB('0F172A');

                    // ── Action column (F) → bold vert ──
                    $sheet->getStyle("F2:F{$lastRow}")
                        ->getFont()->setBold(true)
                        ->getColor()->setRGB('2DB56B');

                    // ── Donnees column (J) → petite police, gris ──
                    $sheet->getStyle("J2:J{$lastRow}")
                        ->getFont()->setSize(8)
                        ->getColor()->setRGB('64748B');

                    // ── Action color coding per row ──
                    for ($row = 2; $row <= $lastRow; $row++) {
                        $action = strtolower((string) $sheet->getCell("F{$row}")->getValue());

                        if (str_contains($action, 'delete') || str_contains($action, 'reject')) {
                            $sheet->getStyle("F{$row}")
                                ->getFont()->getColor()->setRGB('991B1B');
                            $sheet->getStyle("F{$row}")
                                ->getFill()
                                ->setFillType(Fill::FILL_SOLID)
                                ->getStartColor()->setRGB('FEE2E2');
                        } elseif (str_contains($action, 'create') || str_contains($action, 'validate')) {
                            $sheet->getStyle("F{$row}")
                                ->getFont()->getColor()->setRGB('166534');
                            $sheet->getStyle("F{$row}")
                                ->getFill()
                                ->setFillType(Fill::FILL_SOLID)
                                ->getStartColor()->setRGB('DCFCE7');
                        } elseif (str_contains($action, 'update') || str_contains($action, 'edit')) {
                            $sheet->getStyle("F{$row}")
                                ->getFont()->getColor()->setRGB('92400E');
                            $sheet->getStyle("F{$row}")
                                ->getFill()
                                ->setFillType(Fill::FILL_SOLID)
                                ->getStartColor()->setRGB('FEF3C7');
                        } elseif (str_contains($action, 'login') || str_contains($action, 'logout')) {
                            $sheet->getStyle("F{$row}")
                                ->getFont()->getColor()->setRGB('1E40AF');
                            $sheet->getStyle("F{$row}")
                                ->getFill()
                                ->setFillType(Fill::FILL_SOLID)
                                ->getStartColor()->setRGB('DBEAFE');
                        }
                    }

                    // ── Remove outer left and right borders ──
                    $sheet->getStyle("A1:A{$lastRow}")
                        ->getBorders()
                        ->getLeft()
                        ->setBorderStyle(Border::BORDER_NONE);

                    $sheet->getStyle("{$lastCol}1:{$lastCol}{$lastRow}")
                        ->getBorders()
                        ->getRight()
                        ->setBorderStyle(Border::BORDER_NONE);
                }
            },
        ];
    }
}
