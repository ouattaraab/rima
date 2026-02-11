<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('form_options', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type', 30)->index();      // vehicle_type, category, fuel_type, transmission, status, contract_type, coverage_type, color
            $table->string('value', 50);               // The display value
            $table->string('parent_type', 30)->nullable(); // For filtered options (e.g. category depends on vehicle_type)
            $table->string('parent_value', 50)->nullable(); // The parent value to filter by
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['type', 'value']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_options');
    }
};
