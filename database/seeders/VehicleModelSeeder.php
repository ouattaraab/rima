<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\VehicleModel;
use Illuminate\Database\Seeder;

class VehicleModelSeeder extends Seeder
{
    public function run(): void
    {
        $models = [
            'Toyota' => [
                ['name' => 'Hilux', 'category' => 'Pick-up'],
                ['name' => 'Land Cruiser', 'category' => 'Utilitaire'],
                ['name' => 'Corolla', 'category' => 'Berline'],
                ['name' => 'RAV4', 'category' => 'Utilitaire'],
                ['name' => 'Dyna', 'category' => 'Camion'],
                ['name' => 'Camry', 'category' => 'Berline'],
                ['name' => 'Prado', 'category' => 'Utilitaire'],
                ['name' => 'Yaris', 'category' => 'Berline'],
            ],
            'Peugeot' => [
                ['name' => '208', 'category' => 'Berline'],
                ['name' => '308', 'category' => 'Berline'],
                ['name' => '508', 'category' => 'Berline'],
                ['name' => '2008', 'category' => 'Utilitaire'],
                ['name' => '3008', 'category' => 'Utilitaire'],
                ['name' => 'Partner', 'category' => 'Utilitaire'],
                ['name' => 'Boxer', 'category' => 'Camion'],
                ['name' => 'Expert', 'category' => 'Utilitaire'],
            ],
            'Renault' => [
                ['name' => 'Clio', 'category' => 'Berline'],
                ['name' => 'Megane', 'category' => 'Berline'],
                ['name' => 'Duster', 'category' => 'Utilitaire'],
                ['name' => 'Master', 'category' => 'Camion'],
                ['name' => 'Kangoo', 'category' => 'Utilitaire'],
                ['name' => 'Trafic', 'category' => 'Utilitaire'],
                ['name' => 'Symbol', 'category' => 'Berline'],
            ],
            'Nissan' => [
                ['name' => 'Patrol', 'category' => 'Utilitaire'],
                ['name' => 'Navara', 'category' => 'Pick-up'],
                ['name' => 'NP300', 'category' => 'Pick-up'],
                ['name' => 'Almera', 'category' => 'Berline'],
                ['name' => 'X-Trail', 'category' => 'Utilitaire'],
                ['name' => 'Qashqai', 'category' => 'Utilitaire'],
            ],
            'Mitsubishi' => [
                ['name' => 'L200', 'category' => 'Pick-up'],
                ['name' => 'Pajero', 'category' => 'Utilitaire'],
                ['name' => 'Canter', 'category' => 'Camion'],
                ['name' => 'Outlander', 'category' => 'Utilitaire'],
                ['name' => 'ASX', 'category' => 'Utilitaire'],
            ],
            'Ford' => [
                ['name' => 'Ranger', 'category' => 'Pick-up'],
                ['name' => 'Transit', 'category' => 'Camion'],
                ['name' => 'Everest', 'category' => 'Utilitaire'],
                ['name' => 'Focus', 'category' => 'Berline'],
                ['name' => 'EcoSport', 'category' => 'Utilitaire'],
            ],
            'Mercedes-Benz' => [
                ['name' => 'Sprinter', 'category' => 'Camion'],
                ['name' => 'Actros', 'category' => 'Camion'],
                ['name' => 'Classe C', 'category' => 'Berline'],
                ['name' => 'Classe E', 'category' => 'Berline'],
                ['name' => 'Vito', 'category' => 'Utilitaire'],
                ['name' => 'GLC', 'category' => 'Utilitaire'],
            ],
            'Hyundai' => [
                ['name' => 'Tucson', 'category' => 'Utilitaire'],
                ['name' => 'HD72', 'category' => 'Camion'],
                ['name' => 'Accent', 'category' => 'Berline'],
                ['name' => 'i10', 'category' => 'Berline'],
                ['name' => 'Creta', 'category' => 'Utilitaire'],
                ['name' => 'H100', 'category' => 'Camion'],
            ],
            'Kia' => [
                ['name' => 'Picanto', 'category' => 'Berline'],
                ['name' => 'Rio', 'category' => 'Berline'],
                ['name' => 'Sportage', 'category' => 'Utilitaire'],
                ['name' => 'Sorento', 'category' => 'Utilitaire'],
                ['name' => 'K2700', 'category' => 'Camion'],
            ],
            'Suzuki' => [
                ['name' => 'Swift', 'category' => 'Berline'],
                ['name' => 'Vitara', 'category' => 'Utilitaire'],
                ['name' => 'Jimny', 'category' => 'Utilitaire'],
                ['name' => 'Alto', 'category' => 'Berline'],
            ],
            'Honda' => [
                ['name' => 'Civic', 'category' => 'Berline'],
                ['name' => 'CR-V', 'category' => 'Utilitaire'],
                ['name' => 'HR-V', 'category' => 'Utilitaire'],
                ['name' => 'City', 'category' => 'Berline'],
            ],
            'Volkswagen' => [
                ['name' => 'Golf', 'category' => 'Berline'],
                ['name' => 'Polo', 'category' => 'Berline'],
                ['name' => 'Tiguan', 'category' => 'Utilitaire'],
                ['name' => 'Amarok', 'category' => 'Pick-up'],
                ['name' => 'Crafter', 'category' => 'Camion'],
                ['name' => 'Transporter', 'category' => 'Utilitaire'],
            ],
            'BMW' => [
                ['name' => 'Serie 3', 'category' => 'Berline'],
                ['name' => 'Serie 5', 'category' => 'Berline'],
                ['name' => 'X3', 'category' => 'Utilitaire'],
                ['name' => 'X5', 'category' => 'Utilitaire'],
            ],
            'Isuzu' => [
                ['name' => 'D-Max', 'category' => 'Pick-up'],
                ['name' => 'NQR', 'category' => 'Camion'],
                ['name' => 'FRR', 'category' => 'Camion'],
                ['name' => 'mu-X', 'category' => 'Utilitaire'],
            ],
            'Land Rover' => [
                ['name' => 'Defender', 'category' => 'Utilitaire'],
                ['name' => 'Discovery', 'category' => 'Utilitaire'],
                ['name' => 'Range Rover', 'category' => 'Berline'],
            ],
            'Chevrolet' => [
                ['name' => 'Spark', 'category' => 'Berline'],
                ['name' => 'Aveo', 'category' => 'Berline'],
                ['name' => 'Cruze', 'category' => 'Berline'],
                ['name' => 'Trailblazer', 'category' => 'Utilitaire'],
            ],
            'Dacia' => [
                ['name' => 'Logan', 'category' => 'Berline'],
                ['name' => 'Sandero', 'category' => 'Berline'],
                ['name' => 'Duster', 'category' => 'Utilitaire'],
            ],
            'Fiat' => [
                ['name' => 'Punto', 'category' => 'Berline'],
                ['name' => 'Ducato', 'category' => 'Camion'],
                ['name' => 'Doblo', 'category' => 'Utilitaire'],
                ['name' => '500', 'category' => 'Berline'],
            ],
            'Yamaha' => [
                ['name' => 'YBR 125', 'category' => 'Moto'],
                ['name' => 'FZ-S', 'category' => 'Moto'],
                ['name' => 'NMAX', 'category' => 'Moto'],
            ],
            'Bajaj' => [
                ['name' => 'Boxer 150', 'category' => 'Moto'],
                ['name' => 'Pulsar 150', 'category' => 'Moto'],
                ['name' => 'Discover 125', 'category' => 'Moto'],
            ],
        ];

        foreach ($models as $brandName => $brandModels) {
            $brand = Brand::where('name', $brandName)->first();
            if (!$brand) continue;

            foreach ($brandModels as $model) {
                VehicleModel::updateOrCreate(
                    ['brand_id' => $brand->id, 'name' => $model['name']],
                    ['category' => $model['category']]
                );
            }
        }
    }
}
