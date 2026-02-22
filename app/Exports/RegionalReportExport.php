<?php

namespace App\Exports;

use App\Models\Vehicle;
use App\Models\Structure;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithCharts;
use Maatwebsite\Excel\Concerns\WithPreCalculateFormulas;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;
use PhpOffice\PhpSpreadsheet\Chart\PlotArea;
use PhpOffice\PhpSpreadsheet\Chart\Legend;
use PhpOffice\PhpSpreadsheet\Chart\Title;

class RegionalReportExport implements FromCollection, WithHeadings, WithStyles, WithEvents, WithColumnWidths, WithTitle, WithCharts, WithPreCalculateFormulas
{
    protected array $structures;
    protected ?string $dateFrom;
    protected ?string $dateTo;
    protected ?Collection $data = null;

    public function __construct(array $structures = [], ?string $dateFrom = null, ?string $dateTo = null)
    {
        $this->structures = $structures;
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
    }

    public function title(): string
    {
        return 'Rapport Regional';
    }

    protected function getData(): Collection
    {
        if ($this->data !== null) {
            return $this->data;
        }

        $query = Vehicle::selectRaw("vehicles.structure_ci, COALESCE(structures.name, vehicles.structure_ci) as structure_name, count(*) as total")
            ->selectRaw("SUM(CASE WHEN vehicles.form_status = 'validated' THEN 1 ELSE 0 END) as validated")
            ->selectRaw("SUM(CASE WHEN vehicles.form_status = 'synchronized' THEN 1 ELSE 0 END) as synchronized")
            ->selectRaw("SUM(CASE WHEN vehicles.form_status = 'rejected' THEN 1 ELSE 0 END) as rejected")
            ->leftJoin('structures', 'vehicles.structure_ci', '=', 'structures.code')
            ->whereNotNull('vehicles.structure_ci')
            ->where('vehicles.structure_ci', '!=', '');

        if (!empty($this->structures)) {
            $query->whereIn('vehicles.structure_ci', $this->structures);
        }
        if ($this->dateFrom) {
            $query->whereDate('vehicles.collected_at', '>=', $this->dateFrom);
        }
        if ($this->dateTo) {
            $query->whereDate('vehicles.collected_at', '<=', $this->dateTo);
        }

        $this->data = $query->groupBy('vehicles.structure_ci', 'structures.name')
            ->orderByDesc('total')
            ->get();

        return $this->data;
    }

    public function collection(): Collection
    {
        return $this->getData()->map(function ($row) {
            $total = (int) $row->total;
            $validated = (int) $row->validated;
            $synchronized = (int) $row->synchronized;
            $rejected = (int) $row->rejected;
            $rate = $total > 0 ? round(($validated / $total) * 100, 1) : 0;

            return [
                $row->structure_name ?? $row->structure_ci,
                $total,
                $validated,
                $synchronized,
                $rejected,
                $rate / 100,
            ];
        });
    }

    public function headings(): array
    {
        return ['Structure / CI', 'Total', 'Valides', 'En attente', 'Rejetes', 'Taux completude'];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 32,
            'B' => 12,
            'C' => 12,
            'D' => 14,
            'E' => 12,
            'F' => 18,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getParent()->getDefaultStyle()->getFont()
            ->setName('Calibri')
            ->setSize(10);

        $sheet->freezePane('A2');
        $sheet->setAutoFilter('A1:F1');

        $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
        $sheet->getPageSetup()->setFitToWidth(1);
        $sheet->getPageSetup()->setFitToHeight(0);

        return [
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

    public function charts(): array
    {
        $rowCount = $this->getData()->count();

        if ($rowCount === 0) {
            return [];
        }

        // Limit chart to top 20 structures for readability
        $chartLastRow = min($rowCount + 1, 21);
        $sheetTitle = $this->title();

        // Categories = structure names
        $categories = [
            new DataSeriesValues(
                DataSeriesValues::DATASERIES_TYPE_STRING,
                "'{$sheetTitle}'!\$A\$2:\$A\${$chartLastRow}",
                null,
                $chartLastRow - 1,
            ),
        ];

        // Series: Valides (C), En attente (D), Rejetes (E) with semantic colors
        $valides = new DataSeriesValues(
            DataSeriesValues::DATASERIES_TYPE_NUMBER,
            "'{$sheetTitle}'!\$C\$2:\$C\${$chartLastRow}",
            null,
            $chartLastRow - 1,
        );
        $valides->setFillColor('2DB56B');

        $enAttente = new DataSeriesValues(
            DataSeriesValues::DATASERIES_TYPE_NUMBER,
            "'{$sheetTitle}'!\$D\$2:\$D\${$chartLastRow}",
            null,
            $chartLastRow - 1,
        );
        $enAttente->setFillColor('F59E0B');

        $rejetes = new DataSeriesValues(
            DataSeriesValues::DATASERIES_TYPE_NUMBER,
            "'{$sheetTitle}'!\$E\$2:\$E\${$chartLastRow}",
            null,
            $chartLastRow - 1,
        );
        $rejetes->setFillColor('EF4444');

        $values = [$valides, $enAttente, $rejetes];

        // Series labels
        $labels = [
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, "'{$sheetTitle}'!\$C\$1", null, 1),
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, "'{$sheetTitle}'!\$D\$1", null, 1),
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, "'{$sheetTitle}'!\$E\$1", null, 1),
        ];

        $series = new DataSeries(
            DataSeries::TYPE_BARCHART,
            DataSeries::GROUPING_STACKED,
            range(0, count($values) - 1),
            $labels,
            $categories,
            $values,
        );
        $series->setPlotDirection(DataSeries::DIRECTION_BAR);

        $plotArea = new PlotArea(null, [$series]);
        $legend = new Legend(Legend::POSITION_BOTTOM, null, false);
        $chartTitle = new Title('Vehicules par structure');

        $chart = new Chart(
            'regional_chart',
            $chartTitle,
            $legend,
            $plotArea,
        );

        $chartBottom = max($chartLastRow + 2, 22);
        $chart->setTopLeftPosition('H1');
        $chart->setBottomRightPosition('R' . $chartBottom);

        return [$chart];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet   = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();
                $lastCol = 'F';

                // ── Header row ──
                $sheet->getRowDimension(1)->setRowHeight(30);

                $headerColors = [
                    'A' => 'E8F5E9',
                    'B' => 'ECEFF1',
                    'C' => 'DCFCE7',
                    'D' => 'FEF3C7',
                    'E' => 'FEE2E2',
                    'F' => 'E3F2FD',
                ];

                foreach ($headerColors as $col => $color) {
                    $sheet->getStyle("{$col}1")->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setRGB($color);
                }

                if ($lastRow < 2) {
                    return;
                }

                // ── Data rows ──
                $sheet->getStyle("A2:{$lastCol}{$lastRow}")
                    ->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

                $sheet->getStyle("A2:{$lastCol}{$lastRow}")
                    ->getBorders()->getBottom()
                    ->setBorderStyle(Border::BORDER_THIN)
                    ->setColor(new Color('E2E8F0'));

                $sheet->getStyle("A1:{$lastCol}{$lastRow}")
                    ->getBorders()->getVertical()
                    ->setBorderStyle(Border::BORDER_THIN)
                    ->setColor(new Color('E2E8F0'));

                // Zebra
                for ($row = 2; $row <= $lastRow; $row++) {
                    if ($row % 2 === 0) {
                        $sheet->getStyle("A{$row}:{$lastCol}{$row}")
                            ->getFill()->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setRGB('F8FAFC');
                    }
                }

                // Structure bold
                $sheet->getStyle("A2:A{$lastRow}")
                    ->getFont()->setBold(true)->getColor()->setRGB('0F172A');

                // Numbers center
                $sheet->getStyle("B2:E{$lastRow}")
                    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Taux %
                $sheet->getStyle("F2:F{$lastRow}")
                    ->getNumberFormat()->setFormatCode('0.0%');
                $sheet->getStyle("F2:F{$lastRow}")
                    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Status header font colors
                $sheet->getStyle("C1")->getFont()->getColor()->setRGB('166534');
                $sheet->getStyle("D1")->getFont()->getColor()->setRGB('92400E');
                $sheet->getStyle("E1")->getFont()->getColor()->setRGB('991B1B');

                // Color code each data row
                for ($row = 2; $row <= $lastRow; $row++) {
                    $sheet->getStyle("C{$row}")->getFont()->setBold(true)->getColor()->setRGB('166534');
                    $sheet->getStyle("D{$row}")->getFont()->getColor()->setRGB('92400E');
                    $sheet->getStyle("E{$row}")->getFont()->getColor()->setRGB('991B1B');

                    $rate = $sheet->getCell("F{$row}")->getValue();
                    if (is_numeric($rate)) {
                        if ($rate >= 0.8) {
                            $sheet->getStyle("F{$row}")->getFont()->setBold(true)->getColor()->setRGB('166534');
                            $sheet->getStyle("F{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('DCFCE7');
                        } elseif ($rate >= 0.5) {
                            $sheet->getStyle("F{$row}")->getFont()->setBold(true)->getColor()->setRGB('92400E');
                            $sheet->getStyle("F{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FEF3C7');
                        } else {
                            $sheet->getStyle("F{$row}")->getFont()->setBold(true)->getColor()->setRGB('991B1B');
                            $sheet->getStyle("F{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FEE2E2');
                        }
                    }
                }

                // Remove outer borders
                $sheet->getStyle("A1:A{$lastRow}")->getBorders()->getLeft()->setBorderStyle(Border::BORDER_NONE);
                $sheet->getStyle("{$lastCol}1:{$lastCol}{$lastRow}")->getBorders()->getRight()->setBorderStyle(Border::BORDER_NONE);

                // ── Totals row ──
                $totalsRow = $lastRow + 1;
                $sheet->setCellValue("A{$totalsRow}", 'TOTAL');
                $sheet->setCellValue("B{$totalsRow}", "=SUM(B2:B{$lastRow})");
                $sheet->setCellValue("C{$totalsRow}", "=SUM(C2:C{$lastRow})");
                $sheet->setCellValue("D{$totalsRow}", "=SUM(D2:D{$lastRow})");
                $sheet->setCellValue("E{$totalsRow}", "=SUM(E2:E{$lastRow})");
                $sheet->setCellValue("F{$totalsRow}", "=IF(B{$totalsRow}>0,C{$totalsRow}/B{$totalsRow},0)");

                $sheet->getStyle("A{$totalsRow}:{$lastCol}{$totalsRow}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => '0F172A']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E8F5E9']],
                    'borders' => ['top' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '94A3B8']]],
                ]);
                $sheet->getStyle("B{$totalsRow}:E{$totalsRow}")
                    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("F{$totalsRow}")
                    ->getNumberFormat()->setFormatCode('0.0%');
                $sheet->getStyle("F{$totalsRow}")
                    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            },
        ];
    }
}
