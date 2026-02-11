<?php

namespace App\Exports;

use App\Models\Vehicle;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ComplianceSheet implements FromCollection, WithHeadings, WithTitle, WithStyles, ShouldAutoSize, WithEvents
{
    protected string $type;
    protected int $rowCount = 0;
    protected array $criticalRows = [];
    protected array $warningRows = [];

    public function __construct(string $type)
    {
        $this->type = $type;
    }

    public function title(): string
    {
        return match ($this->type) {
            'expired_insurance' => 'Assurance expiree',
            'expired_inspection' => 'Controle technique',
            'no_registration' => 'Sans immatriculation',
            'no_insurance' => 'Sans assurance',
        };
    }

    public function headings(): array
    {
        return ['Immatriculation', 'Marque', 'Modele', 'Type', 'Statut', 'Structure/CI', 'Date concernee', 'Alerte'];
    }

    public function collection(): Collection
    {
        $today = now();
        $query = Vehicle::where('form_status', 'validated');

        $vehicles = match ($this->type) {
            'expired_insurance' => (clone $query)->where('is_insured', true)
                ->whereNotNull('insurance_end_date')
                ->whereDate('insurance_end_date', '<', $today)
                ->orderBy('insurance_end_date')->get(),
            'expired_inspection' => (clone $query)->whereNotNull('technical_inspection_date')
                ->whereDate('technical_inspection_date', '<', $today->copy()->subYear())
                ->orderBy('technical_inspection_date')->get(),
            'no_registration' => (clone $query)->where(function ($q) {
                $q->whereNull('registration_number')->orWhere('registration_number', '');
            })->orderByDesc('collected_at')->get(),
            'no_insurance' => (clone $query)->where('status', 'En service')
                ->where(function ($q) {
                    $q->where('is_insured', false)->orWhereNull('is_insured');
                })->orderByDesc('collected_at')->get(),
        };

        $rowIndex = 2; // Row 1 is headings

        $mapped = $vehicles->map(function ($v) use ($today, &$rowIndex) {
            $date = match ($this->type) {
                'expired_insurance' => $v->insurance_end_date?->format('d/m/Y'),
                'expired_inspection' => $v->technical_inspection_date?->format('d/m/Y'),
                default => $v->collected_at?->format('d/m/Y'),
            };

            $alert = match ($this->type) {
                'expired_insurance' => 'Assurance expiree depuis ' . $v->insurance_end_date?->diffForHumans(),
                'expired_inspection' => 'Controle technique expire',
                'no_registration' => 'Pas d\'immatriculation definitive',
                'no_insurance' => 'Vehicule en service sans assurance',
            };

            // Determine severity for coloring
            $isCritical = false;
            $isWarning = false;

            if ($this->type === 'expired_insurance' && $v->insurance_end_date) {
                $daysSinceExpiry = $v->insurance_end_date->diffInDays($today);
                if ($daysSinceExpiry > 90) {
                    $isCritical = true;
                } else {
                    $isWarning = true;
                }
            } elseif ($this->type === 'expired_inspection' && $v->technical_inspection_date) {
                $monthsSinceExpiry = $v->technical_inspection_date->diffInMonths($today);
                if ($monthsSinceExpiry > 18) {
                    $isCritical = true;
                } else {
                    $isWarning = true;
                }
            } elseif ($this->type === 'no_insurance') {
                $isCritical = true;
            } elseif ($this->type === 'no_registration') {
                $isWarning = true;
            }

            if ($isCritical) {
                $this->criticalRows[] = $rowIndex;
            } elseif ($isWarning) {
                $this->warningRows[] = $rowIndex;
            }

            $rowIndex++;

            return [
                $v->registration_number ?: $v->temporary_registration ?: '-',
                $v->brand, $v->model, $v->vehicle_type, $v->status,
                $v->structure_ci, $date, $alert,
            ];
        });

        $this->rowCount = $mapped->count();

        return $mapped;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FF1F2937']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFE5E7EB'],
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastCol = 'H';

                // Apply red background for critical rows
                foreach ($this->criticalRows as $row) {
                    $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['argb' => 'FFFEE2E2'],
                        ],
                    ]);
                }

                // Apply yellow background for warning rows
                foreach ($this->warningRows as $row) {
                    $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['argb' => 'FFFEF3C7'],
                        ],
                    ]);
                }
            },
        ];
    }
}
