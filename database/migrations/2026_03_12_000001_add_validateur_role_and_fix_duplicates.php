<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ============================================================
        // A1: Add validateur_sodeci role to users ENUM
        // ============================================================
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('agent_cidec', 'supervisor_cidec', 'supervisor_sodeci', 'admin_sodeci', 'finance_dbcg', 'finance_dfc', 'validateur_sodeci') NOT NULL");

        // ============================================================
        // B1: Fix duplicate vehicles + add UNIQUE indexes
        // ============================================================

        // 1. Convert empty strings to NULL (required for UNIQUE index to work)
        DB::statement("UPDATE vehicles SET temporary_registration = NULL WHERE temporary_registration = ''");
        DB::statement("UPDATE vehicles SET chassis_number = NULL WHERE chassis_number = ''");

        // 2. Remove duplicate vehicles (keep the most recently updated one per identifier)
        // Duplicates on temporary_registration (same non-null value)
        DB::statement("
            DELETE v1 FROM vehicles v1
            INNER JOIN vehicles v2
            ON v1.temporary_registration = v2.temporary_registration
            AND v1.temporary_registration IS NOT NULL
            AND v1.id <> v2.id
            AND v1.updated_at < v2.updated_at
        ");

        // Duplicates on chassis_number (same non-null value)
        DB::statement("
            DELETE v1 FROM vehicles v1
            INNER JOIN vehicles v2
            ON v1.chassis_number = v2.chassis_number
            AND v1.chassis_number IS NOT NULL
            AND v1.id <> v2.id
            AND v1.updated_at < v2.updated_at
        ");

        // 3. Add UNIQUE indexes (MySQL allows multiple NULLs in UNIQUE index)
        Schema::table('vehicles', function (Blueprint $table) {
            $table->unique('temporary_registration', 'vehicles_temporary_registration_unique');
            $table->unique('chassis_number', 'vehicles_chassis_number_unique');
        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropUnique('vehicles_temporary_registration_unique');
            $table->dropUnique('vehicles_chassis_number_unique');
        });

        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('agent_cidec', 'supervisor_cidec', 'supervisor_sodeci', 'admin_sodeci', 'finance_dbcg', 'finance_dfc') NOT NULL");
    }
};
