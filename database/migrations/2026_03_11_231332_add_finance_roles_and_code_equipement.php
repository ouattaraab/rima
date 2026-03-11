<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Expand the role enum on users table (MySQL enum requires raw SQL)
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('agent_cidec', 'supervisor_cidec', 'supervisor_sodeci', 'admin_sodeci', 'finance_dbcg', 'finance_dfc') NOT NULL");

        // 2. Add code_equipement to vehicles table
        Schema::table('vehicles', function (Blueprint $table) {
            $table->string('code_equipement', 4)->nullable()->after('code_immo');
        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn('code_equipement');
        });

        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('agent_cidec', 'supervisor_cidec', 'supervisor_sodeci', 'admin_sodeci') NOT NULL");
    }
};
