<?php

namespace App\Exports;

use App\Models\Structure;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class StructuresExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithTitle, WithEvents, WithDrawings, WithCustomStartCell
{
    private const HEADER_COLOR = '2DB56B';
    private const ACTIVE_COLOR = 'E8F5E9';
    private const INACTIVE_COLOR = 'FEE2E2';
    private const ZEBRA_COLOR = 'F8FAFC';
    private const DATA_START_ROW = 5;
    private const DATA_ROW_START = 6;
    private const REGION_COLORS = [
        'DCE8F8', 'E8DCF8', 'F8E8DC', 'DCF8E8', 'F8DCE8',
        'F8F0DC', 'DCF0F8', 'E8E8DC', 'F0DCF8', 'DCF8F0',
    ];

    public function startCell(): string
    {
        return 'A' . self::DATA_START_ROW;
    }

    public function collection(): Collection
    {
        return Structure::with('directionRelation')->orderBy('region')->orderBy('name')->get()->map(function ($item, $index) {
            return [
                $index + 1,
                $item->code,
                $item->name,
                $item->sigle ?? '—',
                $item->directionRelation?->name ?? '—',
                $item->site ?? '—',
                $item->type ?? '—',
                $item->is_active ? 'Oui' : 'Non',
                $item->created_at?->format('d/m/Y H:i'),
            ];
        });
    }

    public function headings(): array
    {
        return ['#', 'CI', 'Exploitation', 'Sigle Dir.', 'Direction', 'Site', 'Type', 'Actif', 'Date création'];
    }

    public function title(): string
    {
        return 'Structures';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 6,
            'B' => 10,
            'C' => 30,
            'D' => 12,
            'E' => 30,
            'F' => 10,
            'G' => 16,
            'H' => 8,
            'I' => 18,
        ];
    }

    public function drawings()
    {
        $logoPath = public_path('logo_sodeci.png');
        if (!file_exists($logoPath)) {
            return [];
        }

        $drawing = new Drawing();
        $drawing->setName('Logo SODECI');
        $drawing->setPath($logoPath);
        $drawing->setHeight(50);
        $drawing->setCoordinates('A1');
        $drawing->setOffsetX(5);
        $drawing->setOffsetY(5);

        return $drawing;
    }

    public function styles(Worksheet $sheet): array
    {
        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $rowCount = $sheet->getHighestRow();
                $lastCol = 'I';
                $headingRow = self::DATA_START_ROW;
                $firstData = self::DATA_ROW_START;

                // ── Title header area ──
                $sheet->getRowDimension(1)->setRowHeight(32);
                $sheet->getRowDimension(2)->setRowHeight(20);
                $sheet->getRowDimension(3)->setRowHeight(18);
                $sheet->getRowDimension(4)->setRowHeight(8);

                $sheet->setCellValue('B1', 'RIMA · SODECI');
                $sheet->getStyle('B1')->applyFromArray([
                    'font' => ['name' => 'Calibri', 'bold' => true, 'size' => 16, 'color' => ['rgb' => '1E293B']],
                    'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                ]);

                $sheet->setCellValue('B2', 'Liste des structures');
                $sheet->getStyle('B2')->applyFromArray([
                    'font' => ['name' => 'Calibri', 'size' => 12, 'color' => ['rgb' => '64748B']],
                    'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                ]);

                $sheet->setCellValue('B3', 'Exporté le ' . now()->format('d/m/Y à H:i'));
                $sheet->getStyle('B3')->applyFromArray([
                    'font' => ['name' => 'Calibri', 'size' => 9, 'italic' => true, 'color' => ['rgb' => '94A3B8']],
                    'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                ]);

                $sheet->getStyle("A4:{$lastCol}4")->applyFromArray([
                    'borders' => ['bottom' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => self::HEADER_COLOR]]],
                ]);

                // ── Default font ──
                $sheet->getStyle("A{$headingRow}:{$lastCol}{$rowCount}")->applyFromArray([
                    'font' => ['name' => 'Calibri', 'size' => 10],
                ]);

                // ── Heading row ──
                $sheet->getStyle("A{$headingRow}:{$lastCol}{$headingRow}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::HEADER_COLOR]],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '229E56']]],
                ]);
                $sheet->getRowDimension($headingRow)->setRowHeight(28);

                // ── Build region color map ──
                $regionColors = [];
                $colorIndex = 0;

                // ── Data rows ──
                for ($row = $firstData; $row <= $rowCount; $row++) {
                    $direction = $sheet->getCell("E{$row}")->getValue();

                    // Assign color per direction
                    if ($direction && $direction !== '—' && !isset($regionColors[$direction])) {
                        $regionColors[$direction] = self::REGION_COLORS[$colorIndex % count(self::REGION_COLORS)];
                        $colorIndex++;
                    }

                    // Direction-based background
                    if ($direction && $direction !== '—' && isset($regionColors[$direction])) {
                        $sheet->getStyle("E{$row}")->applyFromArray([
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $regionColors[$direction]]],
                            'font' => ['bold' => true],
                        ]);
                        if ($row % 2 === 0) {
                            $sheet->getStyle("A{$row}:D{$row}")->applyFromArray([
                                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::ZEBRA_COLOR]],
                            ]);
                            $sheet->getStyle("F{$row}:{$lastCol}{$row}")->applyFromArray([
                                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::ZEBRA_COLOR]],
                            ]);
                        }
                    } else {
                        if ($row % 2 === 0) {
                            $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
                                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::ZEBRA_COLOR]],
                            ]);
                        }
                    }

                    // Borders
                    $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
                        'borders' => [
                            'bottom' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E2E8F0']],
                            'left' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E2E8F0']],
                            'right' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E2E8F0']],
                        ],
                    ]);

                    // Actif column (H)
                    $actifValue = $sheet->getCell("H{$row}")->getValue();
                    $bgColor = $actifValue === 'Oui' ? self::ACTIVE_COLOR : self::INACTIVE_COLOR;
                    $textColor = $actifValue === 'Oui' ? '16A34A' : 'DC2626';
                    $sheet->getStyle("H{$row}")->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bgColor]],
                        'font' => ['bold' => true, 'color' => ['rgb' => $textColor]],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    ]);

                    $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("B{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("D{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("F{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("I{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }

                // ── Total row ──
                $totalRow = $rowCount + 1;
                $dataCount = $rowCount - $headingRow;
                $sheet->setCellValue("B{$totalRow}", "Total : {$dataCount} structure(s)");
                $sheet->getStyle("A{$totalRow}:{$lastCol}{$totalRow}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => '1E293B']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E8F5E9']],
                    'borders' => ['top' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => self::HEADER_COLOR]]],
                ]);

                // Auto-filter & freeze
                $sheet->setAutoFilter("A{$headingRow}:{$lastCol}{$rowCount}");
                $sheet->freezePane('D' . $firstData);
                $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, $headingRow);
            },
        ];
    }
}
