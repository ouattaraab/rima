<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add '01' suffix to ALL vehicles (motos + autos) whose registration
        // ends with a letter (no digit at the end).
        // Examples: AA119KC → AA119KC01, 2399KN → 2399KN01
        // Does NOT touch: 1017KHO1 (ends with digit), AA601NK01 (already has 01)
        $affected = DB::table('vehicles')
            ->whereNotNull('registration_number')
            ->whereRaw("registration_number REGEXP '[A-Za-z]$'")
            ->update([
                'registration_number' => DB::raw("CONCAT(registration_number, '01')"),
            ]);

        DB::statement("SELECT 1"); // force flush
        \Illuminate\Support\Facades\Log::info("Migration: Added '01' suffix to {$affected} vehicle registrations");
    }

    public function down(): void
    {
        // Remove trailing '01' that was added (only where it ends with letter+01)
        DB::table('vehicles')
            ->whereNotNull('registration_number')
            ->whereRaw("registration_number REGEXP '[A-Za-z]01$'")
            ->update([
                'registration_number' => DB::raw("LEFT(registration_number, LENGTH(registration_number) - 2)"),
            ]);
    }
};
