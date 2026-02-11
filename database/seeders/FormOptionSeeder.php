<?php

namespace Database\Seeders;

use App\Models\FormOption;
use Illuminate\Database\Seeder;

class FormOptionSeeder extends Seeder
{
    public function run(): void
    {
        $options = [
            // Vehicle types
            ['type' => 'vehicle_type', 'value' => 'Auto', 'sort_order' => 1],
            ['type' => 'vehicle_type', 'value' => 'Moto', 'sort_order' => 2],

            // Categories (with parent_type = vehicle_type)
            ['type' => 'category', 'value' => 'Utilitaire', 'parent_type' => 'vehicle_type', 'parent_value' => 'Auto', 'sort_order' => 1],
            ['type' => 'category', 'value' => 'Berline', 'parent_type' => 'vehicle_type', 'parent_value' => 'Auto', 'sort_order' => 2],
            ['type' => 'category', 'value' => 'Pick-up', 'parent_type' => 'vehicle_type', 'parent_value' => 'Auto', 'sort_order' => 3],
            ['type' => 'category', 'value' => 'Camion', 'parent_type' => 'vehicle_type', 'parent_value' => 'Auto', 'sort_order' => 4],
            ['type' => 'category', 'value' => 'Moto', 'parent_type' => 'vehicle_type', 'parent_value' => 'Moto', 'sort_order' => 5],

            // Fuel types
            ['type' => 'fuel_type', 'value' => 'Essence', 'sort_order' => 1],
            ['type' => 'fuel_type', 'value' => 'Gasoil', 'sort_order' => 2],
            ['type' => 'fuel_type', 'value' => 'Hybride', 'sort_order' => 3],
            ['type' => 'fuel_type', 'value' => 'Electrique', 'sort_order' => 4],

            // Transmissions
            ['type' => 'transmission', 'value' => 'Automatique', 'sort_order' => 1],
            ['type' => 'transmission', 'value' => 'Manuelle', 'sort_order' => 2],

            // Vehicle statuses
            ['type' => 'status', 'value' => 'En service', 'sort_order' => 1],
            ['type' => 'status', 'value' => 'En reparation', 'sort_order' => 2],
            ['type' => 'status', 'value' => 'Reforme', 'sort_order' => 3],
            ['type' => 'status', 'value' => 'Cede', 'sort_order' => 4],

            // Contract types
            ['type' => 'contract_type', 'value' => 'Sous contrat', 'sort_order' => 1],
            ['type' => 'contract_type', 'value' => 'Flotte', 'sort_order' => 2],

            // Coverage types
            ['type' => 'coverage_type', 'value' => 'Tout risque', 'sort_order' => 1],
            ['type' => 'coverage_type', 'value' => 'Tiers', 'sort_order' => 2],

            // Colors
            ['type' => 'color', 'value' => 'Blanc', 'sort_order' => 1],
            ['type' => 'color', 'value' => 'Noir', 'sort_order' => 2],
            ['type' => 'color', 'value' => 'Gris', 'sort_order' => 3],
            ['type' => 'color', 'value' => 'Bleu', 'sort_order' => 4],
            ['type' => 'color', 'value' => 'Rouge', 'sort_order' => 5],
            ['type' => 'color', 'value' => 'Vert', 'sort_order' => 6],
            ['type' => 'color', 'value' => 'Jaune', 'sort_order' => 7],
            ['type' => 'color', 'value' => 'Beige', 'sort_order' => 8],
            ['type' => 'color', 'value' => 'Marron', 'sort_order' => 9],
            ['type' => 'color', 'value' => 'Autre', 'sort_order' => 99],
        ];

        foreach ($options as $option) {
            FormOption::updateOrCreate(
                ['type' => $option['type'], 'value' => $option['value']],
                $option
            );
        }
    }
}
