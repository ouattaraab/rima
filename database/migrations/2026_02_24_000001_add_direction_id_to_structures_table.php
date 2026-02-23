<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('structures', function (Blueprint $table) {
            $table->uuid('direction_id')->nullable()->after('direction');
            $table->foreign('direction_id')->references('id')->on('directions')->nullOnDelete();
        });

        // Auto-create Direction records from existing structure data
        $distinct = DB::table('structures')
            ->select('sigle', 'direction')
            ->whereNotNull('sigle')
            ->where('sigle', '!=', '')
            ->distinct()
            ->get();

        foreach ($distinct as $d) {
            DB::table('directions')->updateOrInsert(
                ['code' => $d->sigle],
                [
                    'id' => Str::uuid()->toString(),
                    'name' => $d->direction ?? $d->sigle,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        // Link existing structures to their direction
        $structures = DB::table('structures')
            ->whereNotNull('sigle')
            ->where('sigle', '!=', '')
            ->get();

        foreach ($structures as $s) {
            $dir = DB::table('directions')->where('code', $s->sigle)->first();
            if ($dir) {
                DB::table('structures')
                    ->where('id', $s->id)
                    ->update(['direction_id' => $dir->id]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('structures', function (Blueprint $table) {
            $table->dropForeign(['direction_id']);
            $table->dropColumn('direction_id');
        });
    }
};
