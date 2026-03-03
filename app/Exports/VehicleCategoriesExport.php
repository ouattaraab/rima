<?php

namespace App\Exports;

use App\Models\VehicleCategory;
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

class VehicleCategoriesExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithTitle, WithEvents, WithDrawings, WithCustomStartCell
{
    private const HEADER_COLOR = '2DB56B';
    private const ACTIVE_COLOR = 'E8F5E9';
    private const INACTIVE_COLOR = 'FEE2E2';
    private const ZEBRA_COLOR = 'F8FAFC';
    private const TITLE_ROW = 1;
    private const SUBTITLE_ROW = 2;
    private const DATE_ROW = 3;
    private const DATA_START_ROW = 5;
    private const DATA_ROW_START = 6;

    public function startCell(): string
    {
        return 'A' . self::DATA_START_ROW;
    }

    public function collection(): Collection
    {
        return VehicleCategory::orderBy('name')->get()->map(function ($item, $index) {
            return [
                $index + 1,
                $item->name,
                $item->is_active ? 'Oui' : 'Non',
                $item->created_at?->format('d/m/Y H:i'),
            ];
        });
    }

    public function headings(): array
    {
        return ['#', 'Nom', 'Actif', 'Date creation'];
    }

    public function title(): string
    {
        return 'Categories';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8,
            'B' => 35,
            'C' => 12,
            'D' => 22,
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
                $lastCol = 'D';
                $headingRow = self::DATA_START_ROW;
                $firstData = self::DATA_ROW_START;

                // ── Title header area ──
                $sheet->getRowDimension(self::TITLE_ROW)->setRowHeight(32);
                $sheet->getRowDimension(self::SUBTITLE_ROW)->setRowHeight(20);
                $sheet->getRowDimension(self::DATE_ROW)->setRowHeight(18);
                $sheet->getRowDimension(4)->setRowHeight(8);

                $sheet->setCellValue('B' . self::TITLE_ROW, 'RIMA · SODECI');
                $sheet->getStyle('B' . self::TITLE_ROW)->applyFromArray([
                    'font' => ['name' => 'Calibri', 'bold' => true, 'size' => 16, 'color' => ['rgb' => '1E293B']],
                    'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                ]);

                $sheet->setCellValue('B' . self::SUBTITLE_ROW, 'Liste des categories');
                $sheet->getStyle('B' . self::SUBTITLE_ROW)->applyFromArray([
                    'font' => ['name' => 'Calibri', 'size' => 12, 'color' => ['rgb' => '64748B']],
                    'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                ]);

                $sheet->setCellValue('B' . self::DATE_ROW, 'Exporte le ' . now()->format('d/m/Y a H:i'));
                $sheet->getStyle('B' . self::DATE_ROW)->applyFromArray([
                    'font' => ['name' => 'Calibri', 'size' => 9, 'italic' => true, 'color' => ['rgb' => '94A3B8']],
                    'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                ]);

                $sheet->getStyle("A4:{$lastCol}4")->applyFromArray([
                    'borders' => [
                        'bottom' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => self::HEADER_COLOR]],
                    ],
                ]);

                $sheet->getStyle("A{$headingRow}:{$lastCol}{$rowCount}")->applyFromArray([
                    'font' => ['name' => 'Calibri', 'size' => 10],
                ]);

                $sheet->getStyle("A{$headingRow}:{$lastCol}{$headingRow}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::HEADER_COLOR]],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '229E56']],
                    ],
                ]);
                $sheet->getRowDimension($headingRow)->setRowHeight(28);

                for ($row = $firstData; $row <= $rowCount; $row++) {
                    if ($row % 2 === 0) {
                        $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::ZEBRA_COLOR]],
                        ]);
                    }

                    $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
                        'borders' => [
                            'bottom' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E2E8F0']],
                            'left' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E2E8F0']],
                            'right' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E2E8F0']],
                        ],
                    ]);

                    $actifValue = $sheet->getCell("C{$row}")->getValue();
                    $bgColor = $actifValue === 'Oui' ? self::ACTIVE_COLOR : self::INACTIVE_COLOR;
                    $textColor = $actifValue === 'Oui' ? '16A34A' : 'DC2626';
                    $sheet->getStyle("C{$row}")->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bgColor]],
                        'font' => ['bold' => true, 'color' => ['rgb' => $textColor]],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    ]);

                    $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("D{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }

                $totalRow = $rowCount + 1;
                $dataCount = $rowCount - $headingRow;
                $sheet->setCellValue("A{$totalRow}", '');
                $sheet->setCellValue("B{$totalRow}", "Total : {$dataCount} categorie(s)");
                $sheet->getStyle("A{$totalRow}:{$lastCol}{$totalRow}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => '1E293B']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E8F5E9']],
                    'borders' => ['top' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => self::HEADER_COLOR]]],
                ]);

                $sheet->setAutoFilter("A{$headingRow}:{$lastCol}{$rowCount}");
                $sheet->freezePane('A' . $firstData);
                $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, $headingRow);
            },
        ];
    }
}
