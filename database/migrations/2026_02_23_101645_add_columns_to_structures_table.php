<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('structures', function (Blueprint $table) {
            $table->string('sigle', 20)->nullable()->after('name');
            $table->string('direction', 100)->nullable()->after('region');
            $table->string('site', 10)->nullable()->after('direction');
            $table->string('type', 20)->nullable()->after('site');
        });

        // Augmenter la taille de region (certains libelles direction sont longs)
        Schema::table('structures', function (Blueprint $table) {
            $table->string('region', 100)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('structures', function (Blueprint $table) {
            $table->dropColumn(['sigle', 'direction', 'site', 'type']);
            $table->string('region', 50)->nullable()->change();
        });
    }
};
