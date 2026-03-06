<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn('coverage_type');
        });

        Schema::dropIfExists('coverage_types');
    }

    public function down(): void
    {
        Schema::create('coverage_types', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 50)->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('vehicles', function (Blueprint $table) {
            $table->string('coverage_type', 30)->nullable()->after('policy_number');
        });
    }
};
