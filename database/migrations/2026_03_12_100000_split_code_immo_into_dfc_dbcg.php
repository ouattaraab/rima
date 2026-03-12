<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add 2 new columns
        Schema::table('vehicles', function (Blueprint $table) {
            $table->string('code_immo_dfc', 7)->nullable()->after('code_immo');
            $table->string('code_immo_dbcg', 7)->nullable()->after('code_immo_dfc');
        });

        // 2. Copy existing code_immo value to BOTH new columns
        //    (so no data is lost — each team can then correct their own)
        DB::statement("UPDATE vehicles SET code_immo_dfc = code_immo, code_immo_dbcg = code_immo WHERE code_immo IS NOT NULL");

        // 3. Drop old code_immo column and its unique index
        Schema::table('vehicles', function (Blueprint $table) {
            // Drop the unique index first (was added in migration 2026_03_11_143201)
            $table->dropUnique('vehicles_code_immo_unique');
            $table->dropColumn('code_immo');
        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->string('code_immo', 7)->nullable()->after('code_equipement');
        });

        // Copy DFC value back as the canonical code_immo
        DB::statement("UPDATE vehicles SET code_immo = code_immo_dfc WHERE code_immo_dfc IS NOT NULL");

        Schema::table('vehicles', function (Blueprint $table) {
            $table->unique('code_immo', 'vehicles_code_immo_unique');
            $table->dropColumn(['code_immo_dfc', 'code_immo_dbcg']);
        });
    }
};
