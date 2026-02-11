<?php

namespace App\Exports;

use App\Models\Brand;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BrandsExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    public function collection(): Collection
    {
        return Brand::orderBy('name')->get()->map(function ($item) {
            return [
                $item->name,
                $item->is_active ? 'Oui' : 'Non',
                $item->created_at?->format('d/m/Y H:i'),
            ];
        });
    }

    public function headings(): array
    {
        return ['Nom', 'Actif', 'Date creation'];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
