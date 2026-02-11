<?php

namespace App\Exports;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class UsersExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function query(): Builder
    {
        $query = User::query();

        if (!empty($this->filters['search'])) {
            $s = $this->filters['search'];
            $query->where(function ($q) use ($s) {
                $q->where('username', 'like', "%{$s}%")
                  ->orWhere('first_name', 'like', "%{$s}%")
                  ->orWhere('last_name', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%");
            });
        }

        if (!empty($this->filters['role'])) {
            $query->where('role', $this->filters['role']);
        }

        if (!empty($this->filters['organization'])) {
            $query->where('organization', $this->filters['organization']);
        }

        return $query->orderBy('username');
    }

    public function headings(): array
    {
        return [
            'Nom d\'utilisateur',
            'Prenom',
            'Nom',
            'Email',
            'Telephone',
            'Role',
            'Organisation',
            'Region',
            'Statut',
            'Derniere connexion',
            'Date de creation',
        ];
    }

    public function map($user): array
    {
        $roleLabels = [
            'agent_cidec' => 'Agent CIDEC',
            'supervisor_cidec' => 'Superviseur CIDEC',
            'supervisor_sodeci' => 'Superviseur SODECI',
            'admin_sodeci' => 'Admin SODECI',
        ];

        return [
            $user->username,
            $user->first_name,
            $user->last_name,
            $user->email,
            $user->phone ?? '',
            $roleLabels[$user->role] ?? $user->role,
            $user->organization,
            $user->region ?? '',
            $user->is_active ? 'Actif' : 'Inactif',
            $user->last_login_at?->format('d/m/Y H:i') ?? '',
            $user->created_at?->format('d/m/Y H:i') ?? '',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
