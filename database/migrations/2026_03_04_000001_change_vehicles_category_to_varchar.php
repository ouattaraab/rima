<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Convert category from ENUM to VARCHAR to allow dynamic categories
        // from the vehicle_categories reference table (e.g. SUV, 4x4, Minibus).
        DB::statement("ALTER TABLE vehicles MODIFY COLUMN category VARCHAR(50) NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE vehicles MODIFY COLUMN category ENUM('Utilitaire','Berline','Pick-up','Camion','Moto') NOT NULL");
    }
};
