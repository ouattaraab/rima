<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        // Migrate existing single-driver data from vehicles to vehicle_drivers
        $vehicles = DB::table('vehicles')
            ->whereNotNull('user_matricule')
            ->where('user_matricule', '!=', '')
            ->get(['id', 'user_direction', 'user_matricule', 'user_driver_license']);

        foreach ($vehicles as $vehicle) {
            DB::table('vehicle_drivers')->insert([
                'id' => Str::uuid()->toString(),
                'vehicle_id' => $vehicle->id,
                'direction' => $vehicle->user_direction ?? '',
                'matricule' => $vehicle->user_matricule,
                'driver_license' => $vehicle->user_driver_license ?? '',
                'is_primary' => true,
                'position' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        // Data migration — no rollback needed, original columns still exist
    }
};
