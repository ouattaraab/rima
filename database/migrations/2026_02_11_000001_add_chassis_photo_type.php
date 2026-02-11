<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE vehicle_photos MODIFY COLUMN photo_type ENUM('front', 'rear', 'side', 'additional', 'registration_card', 'chassis') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE vehicle_photos MODIFY COLUMN photo_type ENUM('front', 'rear', 'side', 'additional', 'registration_card') NOT NULL");
    }
};
