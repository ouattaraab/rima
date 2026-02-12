<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            // SQLite does not support MODIFY COLUMN / ENUM. The column is
            // already a TEXT in the original migration, so 'chassis' is
            // implicitly accepted. Nothing to do.
            return;
        }

        DB::statement("ALTER TABLE vehicle_photos MODIFY COLUMN photo_type ENUM('front', 'rear', 'side', 'additional', 'registration_card', 'chassis') NOT NULL");
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::statement("ALTER TABLE vehicle_photos MODIFY COLUMN photo_type ENUM('front', 'rear', 'side', 'additional', 'registration_card') NOT NULL");
    }
};
