<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add '01' suffix to moto registrations that don't end with 2 digits
        // e.g. AA862RL → AA862RL01, but AA601NK01 stays AA601NK01
        DB::table('vehicles')
            ->where('vehicle_type', 'Moto')
            ->whereNotNull('registration_number')
            ->whereRaw("registration_number NOT REGEXP '[0-9]{2}$'")
            ->update([
                'registration_number' => DB::raw("CONCAT(registration_number, '01')"),
            ]);
    }

    public function down(): void
    {
        // Remove trailing '01' from motos that were updated
        // Only targets motos where the suffix was recently added
        DB::table('vehicles')
            ->where('vehicle_type', 'Moto')
            ->whereNotNull('registration_number')
            ->whereRaw("registration_number REGEXP '[A-Z]{2}01$'")
            ->update([
                'registration_number' => DB::raw("LEFT(registration_number, LENGTH(registration_number) - 2)"),
            ]);
    }
};
