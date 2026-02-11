<?php

namespace App\Exports;

use App\Models\Vehicle;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RegionalReportExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    protected ?string $region;
    protected ?string $dateFrom;
    protected ?string $dateTo;

    public function __construct(?string $region = null, ?string $dateFrom = null, ?string $dateTo = null)
    {
        $this->region = $region;
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
    }

    public function collection(): Collection
    {
        $query = Vehicle::selectRaw("structure_ci, count(*) as total")
            ->selectRaw("SUM(CASE WHEN form_status = 'validated' THEN 1 ELSE 0 END) as validated")
            ->selectRaw("SUM(CASE WHEN form_status = 'rejected' THEN 1 ELSE 0 END) as rejected")
            ->selectRaw("SUM(CASE WHEN form_status = 'synchronized' THEN 1 ELSE 0 END) as synchronized")
            ->whereNotNull('structure_ci')
            ->where('structure_ci', '!=', '');

        if ($this->region) {
            $query->where('structure_ci', 'like', "%{$this->region}%");
        }
        if ($this->dateFrom) {
            $query->whereDate('collected_at', '>=', $this->dateFrom);
        }
        if ($this->dateTo) {
            $query->whereDate('collected_at', '<=', $this->dateTo);
        }

        return $query->groupBy('structure_ci')
            ->orderByDesc('total')
            ->get()
            ->map(function ($row) {
                $rate = $row->total > 0 ? round(($row->validated / $row->total) * 100, 1) : 0;
                return [
                    $row->structure_ci,
                    $row->total,
                    $row->validated,
                    $row->synchronized,
                    $row->rejected,
                    $rate . '%',
                ];
            });
    }

    public function headings(): array
    {
        return ['Structure / CI', 'Total', 'Valides', 'En attente', 'Rejetes', 'Taux completude'];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFE5E7EB'],
                ],
            ],
        ];
    }
}
